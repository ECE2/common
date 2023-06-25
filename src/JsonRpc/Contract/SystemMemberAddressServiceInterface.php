<?php

namespace Ece2\Common\JsonRpc\Contract;

interface SystemMemberAddressServiceInterface
{
    public function getByIds(array $ids);

    public function getByWhereRaw($sql, $bindings = [], $boolean = 'and');
}
