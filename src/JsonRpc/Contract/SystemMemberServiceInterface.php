<?php

namespace Ece2\Common\JsonRpc\Contract;

interface SystemMemberServiceInterface
{
    public function getByIds(array $ids);

    /** 根据 token 获取用户信息 */
    public function getInfoByJwtToken(string $jwtToken, string $scene = 'member');

    /**
     * 根据ID更新数据.
     * @param int $id
     * @param array $data
     * @return mixed
     */
    public function update(int $id, array $data);
}
