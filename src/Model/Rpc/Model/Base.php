<?php

declare(strict_types=1);

namespace Ece2\Common\Model\Rpc\Model;

use Ece2\Common\Model\Traits\HasRelationshipsForRpc;
use Hyperf\DbConnection\Model\Model as BaseModel;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

abstract class Base extends BaseModel
{
    use HasRelationshipsForRpc;

    protected array $guarded = [];

    /**
     * model 对应的远程 service
     * @return mixed
     */
    abstract protected static function getService();

    /**
     * 用于 rpc 连表查询.
     * @param mixed $ids
     * @return array
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public static function get($ids = [])
    {
        return array_map(
            static fn($model) => new static($model),
            static::getService()?->getByIds($ids)['data'] ?? []
        );
    }

    /**
     * 根据 where 条件获取数据. (同样用于 rpc 连表查询. 需要一些条件的查询情况).
     * @param $sql
     * @param $bindings
     * @param $boolean
     * @return Base[]
     */
    public static function getByWhereRaw($sql, $bindings = [], $boolean = 'and')
    {
        return array_map(
            static fn($model) => new static($model),
            static::getService()?->getByWhereRaw($sql, $bindings, $boolean)['data'] ?? []
        );
    }
}
