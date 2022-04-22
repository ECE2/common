<?php

declare(strict_types=1);

namespace Ece2\Common\Crontab\Mutex;

use Ece2\Common\Crontab\Crontab;

interface ServerMutex
{
    /**
     * 尝试为给定的 crontab 获取服务器互斥锁.
     * @param Crontab $crontab
     * @return bool
     */
    public function attempt(Crontab $crontab): bool;

    /**
     * 获取给定 crontab 的服务器互斥锁.
     * @param Crontab $crontab
     * @return string
     */
    public function get(Crontab $crontab): string;
}
