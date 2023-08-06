<?php

declare(strict_types=1);

namespace Ece2\Common\Model\Rpc\Model;

use Ece2\Common\JsonRpc\Contract\SystemMemberServiceInterface;

use Ece2\Common\Model\Traits\CompanyIsolate;
use function Hyperf\Support\make;

class SystemMember extends Base
{
    use CompanyIsolate;

    /**
     * 是否管理端用户.
     * @return false
     */
    public function isManagement(): bool
    {
        return false;
    }

    /**
     * @return SystemMemberServiceInterface
     */
    protected static function getService()
    {
        return make(SystemMemberServiceInterface::class);
    }

    /**
     * 地址.
     */
    public function address()
    {
        return $this->rpcHasMany(SystemMemberAddress::class, 'member_id');
    }

    /**
     * 向用户发送微信模板消息.
     * @param $templateId
     * @param $data
     * @return mixed
     */
    public function sendWechatTemplateMessage($templateId, $data = [])
    {
        return self::getService()->sendWechatTemplateMessage($this->getKey(), $templateId, $data);
    }
}
