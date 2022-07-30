<?php

declare(strict_types=1);

namespace Ece2\Common\Model\Traits;

use Ece2\Common\Model\Scopes\DataPermissionScope;

/**
 * @method static static|\Hyperf\Database\Model\Builder|\Hyperf\Database\Query\Builder dataPermission(?int $userid = null)
 */
trait DataPermission
{
    /**
     * 注册 scope.
     */
    public static function bootDataPermission()
    {
        static::addGlobalScope(new DataPermissionScope());
    }
}
