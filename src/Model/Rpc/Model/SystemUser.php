<?php

declare(strict_types=1);

namespace Ece2\Common\Model\Rpc\Model;

use Ece2\Common\JsonRpc\Contract\SystemUserServiceInterface;

use function Hyperf\Support\make;

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
     * 通知方式: 短信.
     */
    public const NOTIFY_TYPE_SMS = 1;

    /**
     * 通知方式: 电话.
     */
    public const NOTIFY_TYPE_CALL_PHONE = 2;

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

    protected static function getService()
    {
        return make(SystemUserServiceInterface::class);
    }

    /**
     * 是否管理端用户.
     * @return false
     */
    public function isManagement(): bool
    {
        return true;
    }

//    /**
//     * 非公司人员.
//     */
//    public function nonCompanyUser(): bool
//    {
//        return in_array($this->user_type, [self::TYPE_USER_SUPER_ADMIN, self::TYPE_USER_OPERATION, self::TYPE_USER_GOVERNMENT_REGULATION]);
//    }
//
//    /**
//     * 公司人员.
//     */
//    public function companyUser(): bool
//    {
//        return in_array($this->user_type, [self::TYPE_USER_BUSINESS_GENERALLY, self::TYPE_USER_BUSINESS_MANAGEMENT]);
//    }
//
//    /**
//     * 是否为超级管理员（创始人），用户禁用对创始人没用.
//     */
//    public function isSuperAdmin(): bool
//    {
//        return $this->user_type === self::TYPE_USER_SUPER_ADMIN;
//    }

    /**
     * 用户详情.
     * @return array|mixed
     */
    public function getInfo()
    {
        return self::getService()->getInfo($this->getKey())['data'] ?? [];
    }

    /**
     * 用户角色.
     * @return \Hyperf\Utils\Collection
     */
    public function getRoles()
    {
        // TODO 连表优化
        return collect(self::getService()->getRoles($this->getKey())['data'] ?? [])->map(fn ($item) => new SystemRole($item));
    }

    /**
     * 通知.
     * @param int $type 1:短信 2:电话
     */
    public function notify($type, string $message)
    {
        return self::getService()->sendMessageToUser($this->getKey(), $message, $type);
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
