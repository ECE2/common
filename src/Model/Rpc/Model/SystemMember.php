<?php

declare(strict_types=1);

namespace Ece2\Common\Model\Rpc\Model;

use Ece2\Common\JsonRpc\Contract\SystemMemberServiceInterface;

use function Hyperf\Support\make;

class SystemMember extends Base
{
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
}
