<?php

declare(strict_types=1);

namespace Ece2\Common\Model\Rpc\Model;

use Ece2\Common\JsonRpc\Contract\WarehouseCommodityServiceInterface;

use function Hyperf\Support\make;

class WarehouseCommodity extends Base
{
    protected static function getService()
    {
        return make(WarehouseCommodityServiceInterface::class);
    }
}
