<?php

declare(strict_types=1);

namespace Ece2\Common\Model\Rpc\Model;

use Ece2\Common\JsonRpc\Contract\SystemDeptServiceInterface;
use Ece2\Common\JsonRpc\Contract\SystemUserServiceInterface;
use Hyperf\Di\Annotation\Inject;

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
 * @property SystemDept $department
 */
class SystemUser extends Base
{
    /**
     * !!! 用户类型 前端 Model 也有定义 注意同步修改 !!!
     * 用户类型: 默认.
     */
    public const TYPE_USER_SUPER_ADMIN = '1';

    /**
     * 用户类型: 运营.
     */
    public const TYPE_USER_OPERATION = '2';

    /**
     * 用户类型: 政府监管.
     */
    public const TYPE_USER_GOVERNMENT_REGULATION = '10';

    /**
     * 用户类型: 企业一般用户.
     */
    public const TYPE_USER_BUSINESS_GENERALLY = '100';

    /**
     * 用户类型: 企业管理用户.
     */
    public const TYPE_USER_BUSINESS_MANAGEMENT = '101';

    /**
     * @Inject
     * @var SystemUserServiceInterface
     */
    protected static $service;

    #[Inject]
    protected SystemDeptServiceInterface $systemDeptService;

    /**
     * 非公司人员.
     */
    public function nonCompanyUser(): bool
    {
        return in_array($this->user_type, [self::TYPE_USER_SUPER_ADMIN, self::TYPE_USER_OPERATION, self::TYPE_USER_GOVERNMENT_REGULATION]);
    }

    /**
     * 公司人员.
     */
    public function companyUser(): bool
    {
        return in_array($this->user_type, [self::TYPE_USER_BUSINESS_GENERALLY, self::TYPE_USER_BUSINESS_MANAGEMENT]);
    }

    /**
     * 是否为超级管理员（创始人），用户禁用对创始人没用.
     */
    public function isSuperAdmin(): bool
    {
        return $this->user_type === self::TYPE_USER_SUPER_ADMIN;
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
        // TODO 连表优化
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
     * @return \Hyperf\Database\Model\Relations\HasOne
     */
    public function department()
    {
        return $this->rpcHasOne(SystemDept::class, 'id', 'dept_id');
    }
}
