<?php

declare(strict_types=1);

namespace Ece2\Common\Amqp\Listener;

use App\Service\SystemQueueLogService;
use App\Service\SystemQueueMessageService;
use Ece2\Common\JsonRpc\Contract\SystemQueueLogServiceInterface;
use Ece2\Common\JsonRpc\Contract\SystemQueueMessageServiceInterface;
use Hyperf\Context\Context;
use Ece2\Common\Amqp\Event\AfterProduce;
use Ece2\Common\Amqp\Event\BeforeProduce;
use Ece2\Common\Amqp\Event\FailToProduce;
use Ece2\Common\Amqp\Event\ProduceEvent;
use Ece2\Common\Amqp\Event\WaitTimeout;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Event\Annotation\Listener;

/**
 * 生产队列监听
 */
#[Listener]
class QueueProduceListener implements ListenerInterface
{
    protected SystemQueueLogService|SystemQueueLogServiceInterface $service;

    public function listen(): array
    {
        // 返回一个该监听器要监听的事件数组，可以同时监听多个事件
        return [
            AfterProduce::class,
            BeforeProduce::class,
            ProduceEvent::class,
            FailToProduce::class,
            WaitTimeout::class,
        ];
    }

    /**
     * @param object $event
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Exception
     */
    public function process(object $event)
    {
        $this->setId(snowflake_id());
        if (is_base_system()) {
            $this->service = container()->get(SystemQueueLogService::class);
        } else {
            $this->service = container()->get(SystemQueueLogServiceInterface::class);
        }
        $class = get_class($event);
        $func = lcfirst(trim(strrchr($class, '\\'),'\\'));
        $this->$func($event);
    }

    /**
     * Description:生产前
     * @param object $event
     */
    public function beforeProduce(object $event)
    {
        $queueName = strchr($event->producer->getRoutingKey(), '.', true) . '.queue';

        $id = $this->getId();

        $payload = json_decode($event->producer->payload(), true);

        if (!isset($payload['queue_id'])) {
            $event->producer->setPayload([
                'queue_id' => $id, 'data' => $payload
            ]);
        }

        $this->service->create([
            'id' => $id,
            'exchange_name' => $event->producer->getExchange(),
            'routing_key_name' => $event->producer->getRoutingKey(),
            'queue_name' => $queueName,
            'queue_content' => $event->producer->payload(),
            'delay_time' => $event->delayTime ?? 0,
            'produce_status' => 2, // SystemQueueLog::PRODUCE_STATUS_SUCCESS
        ]);
    }

    /**
     * Description:生产中
     * @param object $event
     */
    public function produceEvent(object $event): void
    {
        // TODO...
    }

    /**
     * Description:生产后
     * @param object $event
     */
    public function afterProduce(object $event): void
    {
        if (isset($event->producer)) {
            $data = json_decode($event->producer->payload(), true)['data'];
            if (is_base_system()) {
                container()->get(SystemQueueMessageService::class)->create($data);
            } else {
                container()->get(SystemQueueMessageServiceInterface::class)->create($data);
            }
        }
    }

    /**
     * Description:生产失败
     */
    public function failToProduce(object $event): void
    {
        $this->service->changeProduceStatus((int) $this->getId(), 3, $event->throwable ?: $event->throwable->getMessage()); // SystemQueueLog::PRODUCE_STATUS_FAIL
    }

    public function setId(string $uuid): void
    {
        Context::set('id', $uuid);
    }

    public function getId(): string
    {
        return Context::get('id', '');
    }
}
