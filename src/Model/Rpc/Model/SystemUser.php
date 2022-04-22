<?php

declare(strict_types=1);

namespace Ece2\Common\Model\Rpc\Model;

use Ece2\Common\JsonRpc\Contract\SystemUserServiceInterface;
use Hyperf\Di\Annotation\Inject;

class SystemUser extends Base
{
    /**
     * @Inject
     * @var SystemUserServiceInterface
     */
    protected static $service;

    /**
     * 是否为超级管理员（创始人），用户禁用对创始人没用.
     */
    public function isSuperAdmin(): bool
    {
        return (int) config('config_center.system.super_admin', env('SUPER_ADMIN')) === (int) $this->getKey();
    }

    /**
     * 是否为管理员角色.
     * @return bool
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function isAdminRole(): bool
    {
        return self::$service->isAdminRole();
    }

    /**
     * 用户详情
     * @return array|mixed
     */
    public function getInfo()
    {
        return self::$service->getInfo($this->getKey())['data'] ?? [];
    }

    /**
     * 用户角色
     * @return array|mixed
     */
    public function getRoles()
    {
        return collect(self::$service->getRoles($this->getKey())['data'] ?? [])->map(fn ($item) => new SystemRole($item));
    }
}
