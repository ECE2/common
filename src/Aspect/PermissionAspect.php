<?php

declare(strict_types=1);

namespace Ece2\Common\Aspect;

use App\Service\SystemUserService;
use Ece2\Common\JsonRpc\Contract\SystemUserServiceInterface;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Di\Exception\Exception;
use Ece2\Common\Annotation\Permission;
use Ece2\Common\Exception\NoPermissionException;
use Hyperf\HttpServer\Request;
use Hyperf\Utils\Arr;

#[Aspect]
class PermissionAspect extends AbstractAspect
{
    public $annotations = [
        Permission::class
    ];

    /**
     * @var int aop 优先级
     */
    public $priority = 95;

    public function __construct(protected Request $request)
    {
    }

    /**
     * @param ProceedingJoinPoint $proceedingJoinPoint
     * @return mixed
     * @throws Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if (identity()?->isSuperAdmin()) {
            return $proceedingJoinPoint->process();
        }

        /** @var Permission $permission */
        if (isset($proceedingJoinPoint->getAnnotationMetadata()->method[Permission::class])) {
            $permission = $proceedingJoinPoint->getAnnotationMetadata()->method[Permission::class];
        }

        // 注解权限为空，则放行
        if (empty($permission->code)) {
            return $proceedingJoinPoint->process();
        }

        $this->checkPermission($permission->code, $permission->where);

        return $proceedingJoinPoint->process();
    }

    /**
     * 检查权限
     * @param string $codeString
     * @param string $where
     * @return bool
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function checkPermission(string $codeString, string $where): bool
    {
        if (is_base_system()) {
            $codes = container()->get(SystemUserService::class)->getInfo()['codes'];
        } else {
            $codes = container()->get(SystemUserServiceInterface::class)->getInfo(identity()?->getKey())['data']['codes'] ?? [];
        }
        // 所有权限
        if (array_search('*', $codes, true) !== false) {
            return true;
        }

        if ($where === 'OR') {
            foreach (explode(',', $codeString) as $code) {
                if (in_array(trim($code), $codes, true)) {
                    return true;
                }
            }

            throw new NoPermissionException(
                t('system.no_permission') . ' -> [ ' . $this->request->getPathInfo() . ' ]'
            );
        }

        if ($where === 'AND') {
            foreach (explode(',', $codeString) as $code) {
                $code = trim($code);
                if (!in_array($code, $codes, true)) {
                    $service = container()->get(\App\Service\SystemMenuService::class);

                    throw new NoPermissionException(
                        t('system.no_permission') . ' -> [ ' . $service->findNameByCode($code) . ' ]'
                    );
                }
            }
        }

        return true;
    }
}
