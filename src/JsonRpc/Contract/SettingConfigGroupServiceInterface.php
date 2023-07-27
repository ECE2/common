<?php

namespace Ece2\Common\JsonRpc\Contract;

interface SettingConfigGroupServiceInterface
{
    /**
     * 新增配置.
     * @param array $data
     * @return array
     */
    public function create($data);

    /**
     * 删除配置.
     * @param mixed $code
     * @return mixed
     */
    public function deleteByCode($code);
}
