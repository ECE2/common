<?php

declare(strict_types=1);

namespace Ece2\Common\Model\Rpc\Model;

use Hyperf\Database\Model\Collection;
use Hyperf\DbConnection\Model\Model as BaseModel;

abstract class Base extends BaseModel
{
    protected $guarded = [];

    protected static $service;

    protected static function getService()
    {
        return static::$service;
    }

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
}
