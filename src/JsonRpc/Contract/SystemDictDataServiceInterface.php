<?php

namespace Ece2\Common\JsonRpc\Contract;

interface SystemDictDataServiceInterface
{
    public function create(array $data);

    public function update($id, array $data);
}
