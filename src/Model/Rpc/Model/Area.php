<?php

declare(strict_types=1);

namespace Ece2\Common\Model\Rpc\Model;

use Ece2\Common\JsonRpc\Contract\AreaServiceInterface;

use function Hyperf\Support\make;

class Area extends Base
{
    protected static function getService()
    {
        return make(AreaServiceInterface::class);
    }
}
