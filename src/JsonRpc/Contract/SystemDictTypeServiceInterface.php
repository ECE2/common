<?php

namespace Ece2\Common\JsonRpc\Contract;

interface SystemDictTypeServiceInterface
{
    public function create(array $data);

    /**
     * 删除字典数据
     * @param mixed $codes 字典标识
     * @return mixed
     */
    public function deleteByCode($codes);
}
