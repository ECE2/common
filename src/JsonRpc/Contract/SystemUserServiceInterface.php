<?php

namespace Ece2\Common\JsonRpc\Contract;

interface SystemUserServiceInterface
{
    public function getByIds(array $ids);

    public function getInfoByJwtToken(string $jwtToken, string $scene = 'admin');

    public function isAdminRole();

    public function getInfo($uid);

    public function getRoles($uid);

    public function getByDeptIds(array $deptIds);

    public function sendMessageToUser($uid, $message, $type);
}
