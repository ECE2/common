<?php

declare(strict_types=1);

namespace Ece2\Common\Model\Traits;

use App\Model\SystemDept;
use App\Model\SystemUser;
use Ece2\Common\Exception\HttpException;
use Ece2\Common\JsonRpc\Contract\SystemDeptServiceInterface;
use Ece2\Common\JsonRpc\Contract\SystemUserServiceInterface;
use Ece2\Common\Model\Rpc\Model\SystemRole;
use Ece2\Common\Model\Rpc\Model\SystemUser as SystemUserForRpc;
use Ece2\Common\Model\Scopes\DataPermissionScope;
use Hyperf\Database\Model\Builder;

/**
 * @method static static|\Hyperf\Database\Model\Builder|\Hyperf\Database\Query\Builder dataPermission(?int $userid = null)
 */
trait DataPermission
{
    /**
     * 注册 scope
     * @return void
     */
    public static function bootDataPermission()
    {
        static::addGlobalScope(new DataPermissionScope());
    }
}
