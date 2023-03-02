<?php

declare(strict_types=1);

namespace Ece2\Common\Model\Rpc\Model;

use App\Model\SystemDept;
use Ece2\Common\JsonRpc\Contract\SystemRoleServiceInterface;
use Hyperf\Di\Annotation\Inject;

class SystemRole extends Base
{
    protected static function getService()
    {
        return make(SystemRoleServiceInterface::class);
    }

    /**
     * 通过中间表获取部门.
     */
    public function getDepts()
    {
        return self::getService()->getDepts($this->getKey())['data'] ?? [];
    }
}
