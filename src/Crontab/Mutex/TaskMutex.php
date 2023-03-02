<?php

declare(strict_types=1);

namespace Ece2\Common\Crontab\Mutex;

use Ece2\Common\Crontab\Crontab;

interface TaskMutex
{
    /**
     * 尝试获取给定 crontab 的任务互斥锁.
     * @param Crontab $crontab
     * @return bool
     */
    public function create(Crontab $crontab): bool;

    /**
     * 确定给定 crontab 是否存在任务互斥锁.
     * @param Crontab $crontab
     * @return bool
     */
    public function exists(Crontab $crontab): bool;

    /**
     * 清除给定 crontab 的任务互斥锁.
     * @param Crontab $crontab
     */
    public function remove(Crontab $crontab);
}
