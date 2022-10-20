<?php

declare(strict_types=1);

namespace Ece2\Common\Aspect;

use App\Service\SystemUserService;
use Ece2\Common\JsonRpc\Contract\SystemUserServiceInterface;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Di\Exception\Exception;
use Ece2\Common\Annotation\Role;
use Ece2\Common\Exception\NoPermissionException;

#[Aspect]
class RoleAspect extends AbstractAspect
{
    public array $annotations = [
        Role::class
    ];

    /**
     * @param ProceedingJoinPoint $proceedingJoinPoint
     * @return mixed
     * @throws Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        // 是超管角色放行
        if (identity()?->isSuperAdmin()) {
            return $proceedingJoinPoint->process();
        }

        /** @var Role $role */
        if (isset($proceedingJoinPoint->getAnnotationMetadata()->method[Role::class])) {
            $role = $proceedingJoinPoint->getAnnotationMetadata()->method[Role::class];
        }

        // 没有使用注解，则放行
        if (empty($role->code)) {
            return $proceedingJoinPoint->process();
        }

        $this->checkRole($role->code, $role->where);

        return $proceedingJoinPoint->process();
    }

    /**
     * 检查角色
     * @param string $codeString
     * @param string $where
     * @return bool
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function checkRole(string $codeString, string $where): bool
    {
        if (is_base_system()) {
            $roles = container()->get(SystemUserService::class)->getInfo()['roles'] ?? [];
        } else {
            $roles = container()->get(SystemUserServiceInterface::class)->getInfo()['data']['roles'] ?? [];
        }

        if ($where === 'OR') {
            foreach (explode(',', $codeString) as $code) {
                if (in_array(trim($code), $roles)) {
                    return true;
                }
            }
            throw new NoPermissionException(
                t('system.no_role') . ' -> [ ' . $codeString . ' ]'
            );
        }

        if ($where === 'AND') {
            foreach (explode(',', $codeString) as $code) {
                $code = trim($code);
                if (!in_array($code, $roles)) {
                    $service = container()->get(\App\Service\SystemRoleService::class);
                    throw new NoPermissionException(
                        t('system.no_role') . ' -> [ ' . $service->findNameByCode($code) . ' ]'
                    );
                }
            }
        }

        return true;
    }
}
