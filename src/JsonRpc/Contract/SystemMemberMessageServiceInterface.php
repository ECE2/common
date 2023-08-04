<?php

namespace Ece2\Common\JsonRpc\Contract;

interface SystemMemberMessageServiceInterface
{
    /**
     * 新增用户消息.
     * @param array $data
     * @return mixed
     */
    public function create($data);
}
