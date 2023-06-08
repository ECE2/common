<?php

declare(strict_types=1);

namespace Ece2\Common\JsonRpc\Contract;

interface WarehouseCommodityServiceInterface
{
    public function getByIds(array $ids);

    /**
     * 商品列表.
     * @param $name
     * @param $numbering
     * @param $categoryId
     * @param array $pageInfo
     * @return array
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function list($name, $numbering, $categoryId, array $pageInfo = []);
}
