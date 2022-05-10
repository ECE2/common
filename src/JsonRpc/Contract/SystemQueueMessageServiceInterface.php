<?php

namespace Ece2\Common\JsonRpc\Contract;

interface SystemQueueMessageServiceInterface
{
    public function create(array $data);
}
