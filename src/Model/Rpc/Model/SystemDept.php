<?php

declare(strict_types=1);

namespace Ece2\Common\Model\Rpc\Model;

use Ece2\Common\JsonRpc\Contract\SystemDeptServiceInterface;
use Hyperf\Di\Annotation\Inject;

class SystemDept extends Base
{
    /**
     * @Inject
     * @var SystemDeptServiceInterface
     */
    protected static $service;
}
