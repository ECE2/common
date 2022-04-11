<?php

declare(strict_types=1);

namespace Ece2\Common\Model\Rpc\Model;

use Ece2\Common\JsonRpc\Contract\AdministratorServiceInterface;
use Hyperf\Di\Annotation\Inject;

class Administrator extends Base
{
    /**
     * @Inject
     * @var AdministratorServiceInterface
     */
    protected static $service;

    public function menusSameLevel()
    {
        return static::$service->getMenusSameLevel($this->getKey())['data'] ?? [];
    }
}
