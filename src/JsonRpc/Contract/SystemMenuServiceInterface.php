<?php

namespace Ece2\Common\JsonRpc\Contract;

interface SystemMenuServiceInterface
{
    public function findNameByCode(string $code);
}
