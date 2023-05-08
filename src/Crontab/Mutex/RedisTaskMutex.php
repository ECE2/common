<?php

declare(strict_types=1);

namespace Ece2\Common\Crontab\Mutex;

use Ece2\Common\Crontab\Crontab;
use Hyperf\Redis\RedisFactory;

use function Hyperf\Support\env;

class RedisTaskMutex implements TaskMutex
{
    public function __construct(private RedisFactory $redisFactory)
    {
    }

    /**
     * 尝试获取给定 crontab 的任务互斥锁.
     * @param Crontab $crontab
     * @return bool
     */
    public function create(Crontab $crontab): bool
    {
        return (bool) $this->redisFactory
            ->get($crontab->getMutexPool())
            ->set(
                $this->getMutexName($crontab),
                $crontab->getName(),
                ['NX', 'EX' => $crontab->getMutexExpires()]
            );
    }

    /**
     * 确定给定 crontab 是否存在任务互斥锁.
     * @param Crontab $crontab
     * @return bool
     */
    public function exists(Crontab $crontab): bool
    {
        return (bool) $this->redisFactory
            ->get($crontab->getMutexPool())
            ->exists(
                $this->getMutexName($crontab)
            );
    }

    /**
     * 清除给定 crontab 的任务互斥锁.
     * @param Crontab $crontab
     */
    public function remove(Crontab $crontab)
    {
        $this->redisFactory
            ->get($crontab->getMutexPool())
            ->del(
                $this->getMutexName($crontab)
            );
    }

    protected function getMutexName(Crontab $crontab): string
    {
        return env('APP_NAME', '') . DIRECTORY_SEPARATOR . 'crontab-' . sha1($crontab->getName() . $crontab->getRule());
    }
}
