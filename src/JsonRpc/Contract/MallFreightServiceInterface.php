<?php

declare(strict_types=1);

namespace Ece2\Common\JsonRpc\Contract;

interface MallFreightServiceInterface
{
    /**
     * 添加 company 运费相关.
     * @param array $data
     * @return mixed
     */
    public function addCompanyFreight(array $data);
}
