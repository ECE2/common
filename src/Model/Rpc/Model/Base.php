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

    protected function getService()
    {
        return $this->service;
    }
}
