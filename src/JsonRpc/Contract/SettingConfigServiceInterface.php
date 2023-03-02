<?php

namespace Ece2\Common\JsonRpc\Contract;

interface SettingConfigServiceInterface
{
    public function getConfigByKey(string $key): array;
}
