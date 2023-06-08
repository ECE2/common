<?php

declare(strict_types=1);

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
