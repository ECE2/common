<?php

namespace Ece2\Common\JsonRpc\Contract;

interface SystemMemberServiceInterface
{
    public function getByIds(array $ids);

    /** 根据 token 获取用户信息 */
    public function getInfoByJwtToken(string $jwtToken, string $scene = 'member');
}