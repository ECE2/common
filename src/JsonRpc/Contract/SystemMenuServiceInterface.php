<?php

namespace Ece2\Common\JsonRpc\Contract;

interface SystemMenuServiceInterface
{
    public function create($menu);
    
    public function findNameByCode(string $code);

    public function deleteByModuleCode($name);

    public function getMaxId();
}
