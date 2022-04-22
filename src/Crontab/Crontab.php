<?php

namespace Ece2\Common\Crontab;

use Hyperf\Crontab\Crontab as BaseCrontab;

class Crontab extends BaseCrontab
{
    /**
     * 失败策略
     * @var string
     */
    protected string $failPolicy = '3';

    /**
     * 调用参数
     * @var string
     */
    protected string $parameter;

    /**
     * 任务ID
     * @var integer
     */
    protected int $crontabId;

    /**
     * @return string
     */
    public function getFailPolicy(): string
    {
        return $this->failPolicy;
    }

    /**
     * @param string $failPolicy
     */
    public function setFailPolicy(string $failPolicy): void
    {
        $this->failPolicy = $failPolicy;
    }

    /**
     * @return string
     */
    public function getParameter(): string
    {
        return $this->parameter;
    }

    /**
     * @param string $parameter
     */
    public function setParameter(string $parameter): void
    {
        $this->parameter = $parameter;
    }

    /**
     * @return int
     */
    public function getCrontabId(): int
    {
        return $this->crontabId;
    }

    /**
     * @param int $crontab_id
     */
    public function setCrontabId(int $crontabId): void
    {
        $this->crontabId = $crontabId;
    }
}
