<?php

namespace Ece2\Common\JsonRpc\Contract;

interface SystemCompanyServiceInterface
{
    public function getByIds(array $ids);

    public function getCompanyBySubDomain(string $subDomain);
}