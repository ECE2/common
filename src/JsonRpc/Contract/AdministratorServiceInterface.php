<?php

namespace Ece2\Common\JsonRpc\Contract;

interface AdministratorServiceInterface
{
    public function getByIds(array $ids);

    public function getInfoByJwtToken(string $jwtToken);

    public function getMenusSameLevel(int $adminId);
}
