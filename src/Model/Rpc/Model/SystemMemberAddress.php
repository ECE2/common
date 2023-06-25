<?php

declare(strict_types=1);

namespace Ece2\Common\Model\Rpc\Model;

use Ece2\Common\JsonRpc\Contract\SystemMemberAddressServiceInterface;

use function Hyperf\Support\make;

class SystemMemberAddress extends Base
{
    protected static function getService()
    {
        return make(SystemMemberAddressServiceInterface::class);
    }
}
