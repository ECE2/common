<?php

namespace Ece2\Common\JsonRpc\Contract;

interface SettingCrontabServiceInterface
{
    public function getRunningTask();

    public function create($data): int|string;
}
