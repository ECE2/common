<?php

declare(strict_types=1);

namespace Ece2\Common\Crontab;

class CrontabScheduler
{
    protected \SplQueue $schedules;

    public function __construct(protected CrontabManage $crontabManager)
    {
        $this->schedules = new \SplQueue();
    }

    public function schedule(): \SplQueue
    {
        foreach ($this->getSchedules() ?? [] as $schedule) {
            $this->schedules->enqueue($schedule);
        }
        return $this->schedules;
    }

    protected function getSchedules(): array
    {
        return $this->crontabManager->getCrontabList();
    }
}
