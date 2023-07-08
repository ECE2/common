<?php

declare(strict_types=1);

namespace Ece2\Common\Model\Rpc\Model;

use Ece2\Common\JsonRpc\Contract\InsuredServiceInterface;
use function Hyperf\Support\make;

class Insured extends Base
{
    protected static function getService()
    {
        return make(InsuredServiceInterface::class);
    }
}
