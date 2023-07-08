<?php

namespace Ece2\Common\JsonRpc\Contract;

interface InsuredServiceInterface
{
    /**
     * 判断指定参保人是否已经通过审核,
     * @param int $insuredId 参保人ID
     * @param int $memberId 当前查询者用户ID
     * @return mixed
     */
    public function isVerified($insuredId, $memberId = 0);
}
