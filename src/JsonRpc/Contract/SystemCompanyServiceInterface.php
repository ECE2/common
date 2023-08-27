<?php

namespace Ece2\Common\JsonRpc\Contract;

interface SystemCompanyServiceInterface
{
    public function getByIds(array $ids);

    public function getCompanyBySubDomain(string $subDomain);

    /**
     * 根据省市名获取企业 (优先市, 如果没有就查省, 再没有就查默认企业).
     * @param $provinceName
     * @param $cityName
     * @return mixed
     */
    public function getCompanyByProvinceCityName($provinceName, $cityName);
}
