<?php

declare(strict_types=1);

namespace Ece2\Common\Aspect;

use App\Service\SystemMenuService;
use Carbon\Carbon;
use Ece2\Common\Annotation\OperationLog;
use Ece2\Common\Annotation\Permission;
use Ece2\Common\Event\Operation;
use Ece2\Common\JsonRpc\Contract\SystemMenuServiceInterface;
use Hyperf\Cache\Annotation\Cacheable;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\HttpServer\Request;

#[Aspect]
class OperationLogAspect extends AbstractAspect
{
    public array $annotations = [
        OperationLog::class
    ];

    /**
     * @param ProceedingJoinPoint $proceedingJoinPoint
     * @return mixed|void
     * @throws \Hyperf\Di\Exception\Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $annotation = $proceedingJoinPoint->getAnnotationMetadata()->method[OperationLog::class];

        if (!empty($annotation->menuName) || ($annotation = $proceedingJoinPoint->getAnnotationMetadata()->method[Permission::class])) {
            event(new Operation($this->getRequestInfo([
                'code' => !empty($annotation->code) ? explode(',', $annotation->code)[0] : '',
                'name' => $annotation->menuName ?? '',
            ])));
        }

        return $proceedingJoinPoint->process();
    }

    /**
     * @param array $data
     * @return array
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function getRequestInfo(array $data): array
    {
        $request = container()->get(Request::class);
        $serverParams = $request->getServerParams();

        $operationLog = [
            'time' => Carbon::parse($serverParams['request_time'])->format('Y-m-d H:i:s'),
            'method' => $serverParams['request_method'],
            'router' => $serverParams['path_info'],
            'protocol' => $serverParams['server_protocol'],
            'ip' => $ip = ip($request),
            'ip_location' => ip_to_region($ip),
            'service_name' => $data['name'] ?: $this->getOperationMenuName($data['code']),
            'request_data' => $request->all(),
        ];
        try {
            $operationLog['username'] = identity()['username'] ?? '';
        } catch (\Exception $e) {
            $operationLog['username'] = t('system.no_login_user');
        }

        return $operationLog;
    }

    /**
     * 获取菜单名称
     * @param string $code
     * @return string
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    // TODO 清除缓存
    #[Cacheable(prefix: 'findMenuNameByCode', value: 'menuCode_#{code}', ttl: 0)]
    protected function getOperationMenuName(string $code): string
    {
        if (is_base_system()) {
            return container()->get(SystemMenuService::class)->findNameByCode($code);
        }

        return container()->get(SystemMenuServiceInterface::class)->findNameByCode($code)['data']['name'] ?? '';
    }
}
