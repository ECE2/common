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
}
