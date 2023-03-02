<?php

declare(strict_types=1);

namespace Ece2\Common\Amqp\Listener;

use App\Service\SystemQueueLogService;
use Ece2\Common\Amqp\Event\AfterConsume;
use Ece2\Common\Amqp\Event\BeforeConsume;
use Ece2\Common\Amqp\Event\ConsumeEvent;
use Ece2\Common\Amqp\Event\FailToConsume;
use Ece2\Common\Amqp\Event\WaitTimeout;
use Ece2\Common\JsonRpc\Contract\SystemQueueLogServiceInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Event\Annotation\Listener;

/**
 * 消费队列监听
 */
#[Listener]
class QueueConsumeListener implements ListenerInterface
{
    private SystemQueueLogService|SystemQueueLogServiceInterface $service;

    public function listen(): array
    {
        // 返回一个该监听器要监听的事件数组，可以同时监听多个事件
        return [
            AfterConsume::class,
            BeforeConsume::class,
            ConsumeEvent::class,
            FailToConsume::class,
            WaitTimeout::class,
        ];
    }

    /**
     * @param object $event
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function process(object $event): void
    {
        if (is_base_system()) {
            $this->service = container()->get(SystemQueueLogService::class);
        } else {
            $this->service = container()->get(SystemQueueLogServiceInterface::class);
        }

        if ($event->message) {
            $class = get_class($event);
            $func = lcfirst(trim(strrchr($class, '\\'),'\\'));
            $this->$func($event);
        }
    }

    /**
     * 消费前
     * @param object $event
     * @return void
     */
    public function beforeConsume(object $event)
    {
        $this->service->changeConsumeStatus((int) $event->data['queue_id'], 1); // SystemQueueLog::CONSUME_STATUS_DOING
    }

    /**
     * 消费中
     * @param object $event
     * @return void
     */
    public function consumeEvent(object $event)
    {
        // TODO...
    }

    /**
     * 消费后
     * @param object $event
     * @return void
     */
    public function afterConsume(object $event)
    {
        $this->service->changeConsumeStatus((int) $event->data['queue_id'], 2); // SystemQueueLog::CONSUME_STATUS_SUCCESS
    }

    /**
     * 消费失败
     * @param object $event
     * @return void
     */
    public function failToConsume(object $event)
    {
        $this->service->changeConsumeStatus((int) $event->data['queue_id'], 4, $event->throwable ?: $event->throwable->getMessage()); // SystemQueueLog::CONSUME_STATUS_4
    }
}
