<?php

declare(strict_types=1);

namespace Ece2\Common\JsonRpc\Contract;

interface WarehouseCommodityCategoryServiceInterface
{
    public function getByIds(array $ids);

    /**
     * 商品分类.
     * @return array
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function list();
}
