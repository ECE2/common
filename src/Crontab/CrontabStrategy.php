<?php

declare(strict_types=1);

namespace Ece2\Common\Crontab;

use Carbon\Carbon;
use Hyperf\Di\Annotation\Inject;

class CrontabStrategy
{
    #[Inject]
    protected CrontabManage $crontabManage;

    #[Inject]
    protected Executor $executor;

    /**
     * @param Crontab $crontab
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function dispatch(Crontab $crontab)
    {
        co(function () use ($crontab) {
            if ($crontab->getExecuteTime() instanceof Carbon) {
                $wait = $crontab->getExecuteTime()->getTimestamp() - time();
                $wait > 0 && \Swoole\Coroutine::sleep($wait);
                $this->executor->execute($crontab);
            }
        });
    }

    /**
     * 执行一次
     * @param Crontab $crontab
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function executeOnce(Crontab $crontab)
    {
        co(function () use ($crontab) {
            $this->executor->execute($crontab);
        });
    }
}
