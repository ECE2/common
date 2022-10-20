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

    protected static $service;

    /**
     * 用于 rpc 连表查询.
     * @param mixed $ids
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @return array
     */
    public static function get($ids = [])
    {
        return array_map(
            static fn ($model) => new static($model),
            static::getService()->getByIds($ids)['data'] ?? []
        );
    }

    protected static function getService()
    {
        return static::$service;
    }
}
