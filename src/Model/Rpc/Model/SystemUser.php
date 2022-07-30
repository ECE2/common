<?php

declare(strict_types=1);

namespace Ece2\Common\Model\Rpc\Model;

use Ece2\Common\JsonRpc\Contract\SystemDeptServiceInterface;
use Ece2\Common\JsonRpc\Contract\SystemUserServiceInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Utils\Arr;

/**
 * @property int $id
 * @property string $username
 * @property string $password
 * @property string $user_type
 * @property string $nickname
 * @property string $phone
 * @property string $email
 * @property string $avatar
 * @property string $signed
 * @property string $dashboard
 * @property int $dept_id
 * @property string $status
 * @property string $login_ip
 * @property string $login_time
 * @property string $backend_setting
 * @property string $remark
 * @property int $created_by
 * @property int $updated_by
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 */
class SystemUser extends Base
{
    /**
     * @Inject
     * @var SystemUserServiceInterface
     */
    protected static $service;

    #[Inject]
    protected SystemDeptServiceInterface $systemDeptService;

    /**
     * 是否为超级管理员（创始人），用户禁用对创始人没用.
     */
    public function isSuperAdmin(): bool
    {
        return (int) config('config_center.system.super_admin', env('SUPER_ADMIN')) === (int) $this->getKey();
    }

    /**
     * 是否为管理员角色.
     */
    public function isAdminRole(): bool
    {
        return self::$service->isAdminRole();
    }

    /**
     * 用户详情.
     * @return array|mixed
     */
    public function getInfo()
    {
        return self::$service->getInfo($this->getKey())['data'] ?? [];
    }

    /**
     * 用户角色.
     * @return \Hyperf\Utils\Collection
     */
    public function getRoles()
    {
        return collect(self::$service->getRoles($this->getKey())['data'] ?? [])->map(fn ($item) => new SystemRole($item));
    }

    /**
     * 发送消息.
     * @param $message
     * @param $type
     * @return mixed
     */
    public function sendMessage($message, $type = 1)
    {
        return self::$service->sendMessageToUser($this->getKey(), $message, $type);
    }

    /**
     * 所属部门.
     * @return SystemDept
     */
    public function department()
    {
        return new SystemDept(Arr::first($this->systemDeptService->getByIds([$this->dept_id])['data'] ?? []));
    }
}
