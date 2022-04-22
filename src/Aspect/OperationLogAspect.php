<?php

declare(strict_types=1);

namespace Ece2\Common\Aspect;

use App\Service\SystemMenuService;
use Ece2\Common\JsonRpc\Contract\SystemMenuServiceInterface;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Ece2\Common\Annotation\OperationLog;
use Ece2\Common\Annotation\Permission;
use Ece2\Common\Event\Operation;
use Ece2\Common\Helper\Str;
use Ece2\Common\Request;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;

#[Aspect]
class OperationLogAspect extends AbstractAspect
{
    public $annotations = [
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
        /* @var $result ResponseInterface */
        $result = $proceedingJoinPoint->process();
        $isDownload = false;
        if (!empty($annotation->menuName) || ($annotation = $proceedingJoinPoint->getAnnotationMetadata()->method[Permission::class])) {
            if (!empty($result->getHeader('content-description')) && !empty($result->getHeader('content-transfer-encoding'))) {
                $isDownload = true;
            }
            $evDispatcher = container()->get(EventDispatcherInterface::class);
            $evDispatcher->dispatch(new Operation($this->getRequestInfo([
                'code' => !empty($annotation->code) ? explode(',', $annotation->code)[0] : '',
                'name' => $annotation->menuName ?? '',
                'response_code' => $result->getStatusCode(),
                'response_data' => $isDownload ? '文件下载' : $result->getBody()->getContents()
            ])));
        }
        return $result;
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

        $operationLog = [
            'time' => date('Y-m-d H:i:s', $request->getServerParams()['request_time']),
            'method' => $request->getServerParams()['request_method'],
            'router' => $request->getServerParams()['path_info'],
            'protocol' => $request->getServerParams()['server_protocol'],
            'ip' => $request->ip(),
            'ip_location' => Str::ipToRegion($request->ip()),
            'service_name' => $data['name'] ?: $this->getOperationMenuName($data['code']),
            'request_data' => $request->all(),
            'response_code' => $data['response_code'],
            'response_data' => $data['response_data'],
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
    protected function getOperationMenuName(string $code): string
    {
        if (is_base_system()) {
            return container()->get(SystemMenuService::class)->findNameByCode($code);
        }

        return container()->get(SystemMenuServiceInterface::class)->findNameByCode($code)['data']['name'] ?? '';
    }
}
