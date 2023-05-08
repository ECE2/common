<?php

declare(strict_types=1);

namespace Ece2\Common\Crontab\Mutex;

use Ece2\Common\Crontab\Crontab;
use Hyperf\Redis\RedisFactory;
use Hyperf\Utils\Arr;
use Hyperf\Utils\Composer;
use Hyperf\Utils\Coordinator\Constants;
use Hyperf\Utils\Coordinator\CoordinatorManager;
use Hyperf\Utils\Coroutine;

use function Hyperf\Support\env;

class RedisServerMutex implements ServerMutex
{
    private null|string $macAddress;

    public function __construct(private RedisFactory $redisFactory)
    {
        $this->macAddress = $this->getMacAddress();
    }

    /**
     * 尝试获取给定 crontab 的服务器互斥锁.
     * @param Crontab $crontab
     * @return bool
     */
    public function attempt(Crontab $crontab): bool
    {
        if ($this->macAddress === null) {
            return false;
        }

        $redis = $this->redisFactory->get($crontab->getMutexPool());
        $mutexName = $this->getMutexName($crontab);
        if ((bool) $redis->set($mutexName, $this->macAddress, ['NX', 'EX' => $crontab->getMutexExpires()])) {
            Coroutine::create(function () use ($crontab, $redis, $mutexName) {
                $exited = CoordinatorManager::until(Constants::WORKER_EXIT)->yield($crontab->getMutexExpires());
                $exited && $redis->del($mutexName);
            });

            return true;
        }

        return $redis->get($mutexName) === $this->macAddress;
    }

    /**
     * 获取给定 crontab 的服务器互斥锁.
     * @param Crontab $crontab
     * @return string
     */
    public function get(Crontab $crontab): string
    {
        return (string) $this->redisFactory
            ->get($crontab->getMutexPool())
            ->get($this->getMutexName($crontab));
    }

    protected function getMutexName(Crontab $crontab): string
    {
        return env('APP_NAME', '') . DIRECTORY_SEPARATOR . 'crontab-' . sha1($crontab->getName() . $crontab->getRule()) . '-sv';
    }

    protected function getMacAddress(): ?string
    {
        $macAddresses = swoole_get_local_mac();

        foreach (Arr::wrap($macAddresses) as $name => $address) {
            if ($address && $address !== '00:00:00:00:00:00') {
                return $name . ':' . str_replace(':', '', $address);
            }
        }

        return null;
    }
}
