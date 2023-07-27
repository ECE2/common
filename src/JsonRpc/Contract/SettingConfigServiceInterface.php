<?php

namespace Ece2\Common\JsonRpc\Contract;

interface SettingConfigServiceInterface
{
    /**
     * 根据 key 获取配置.
     * @param string $key
     * @return array
     */
    public function getConfigByKey(string $key): array;

    /**
     * 根据组获取配置.
     * @param array $codes
     * @return array
     */
    public function getConfigByGroup(array $codes): array;
}
