<?php

declare(strict_types=1);

namespace Ece2\Common\Amqp\Event;

use Hyperf\Amqp\Message\ConsumerMessageInterface;
use Hyperf\Amqp\Message\ProducerMessageInterface;
use Throwable;

class FailToProduce extends ConsumeEvent
{
    public function __construct(ProducerMessageInterface $producer, public Throwable $throwable)
    {
        $this->throwable = $throwable;
    }

    public function getThrowable(): Throwable
    {
        return $this->throwable;
    }
}
