<?php

namespace Ece2\Common\JsonRpc\Contract;

interface SystemUserServiceInterface
{
    public function getInfoByJwtToken(string $jwtToken, string $scene = 'admin');

    public function isAdminRole();

    public function getInfo(int $uid);

    public function getRoles(int $uid);

    public function getByDeptIds(array $deptIds);
}
