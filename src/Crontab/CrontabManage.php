<?php

declare(strict_types=1);

namespace Ece2\Common\Crontab;

use App\Service\SettingCrontabService;
use Ece2\Common\JsonRpc\Contract\SettingCrontabServiceInterface;
use Hyperf\Crontab\Parser;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Guzzle\ClientFactory;
use Hyperf\Redis\Redis;
use Psr\Container\ContainerInterface;

/**
 * 定时任务管理器
 */
class CrontabManage
{
    #[Inject]
    protected Parser $parser;

    #[Inject]
    protected ClientFactory $clientFactory;

    #[Inject]
    protected Redis $redis;

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __construct()
    {
    }

    /**
     * 获取定时任务列表
     * @return array
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getCrontabList(): array
    {
        $prefix = config('cache.default.prefix');
        $data = $this->redis->get($prefix . 'crontab');
        if ($data === false) {
            if (is_base_system()) {
                $data = container()->get(SettingCrontabService::class)->getRunningTask();
            } else {
                $data = container()->get(SettingCrontabServiceInterface::class)->getRunningTask()['data'] ?? [];
            }
            $this->redis->set($prefix . 'crontab', serialize($data));
        } else {
            $data = unserialize($data);
        }
        if (is_null($data)) {
            return [];
        }

        $last = time();
        $list = [];
        foreach ($data as $item) {
            $crontab = new Crontab();
            $crontab->setCallback($item['target']);
            $crontab->setType($item['type']);
            $crontab->setEnable(true);
            $crontab->setCrontabId($item['id']);
            $crontab->setName($item['name']);
            $crontab->setParameter($item['parameter'] ?: '');
            $crontab->setRule($item['rule']);

            if (!$this->parser->isValid($crontab->getRule())) {
                console()->info('Crontab task [' . $item['name'] . '] rule error, skipping execution');
                continue;
            }

            $time = $this->parser->parse($crontab->getRule(), $last);
            if ($time) {
                foreach ($time as $t) {
                    $list[] = clone $crontab->setExecuteTime($t);
                }
            }
        }

        return $list;
    }
}
