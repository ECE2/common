<?php

declare(strict_types=1);

namespace Ece2\Common\Amqp\Event;

use Hyperf\Amqp\Message\ConsumerMessageInterface;
use Throwable;

class FailToConsume
{
    public function __construct(public ConsumerMessageInterface $message, public $data, public Throwable $throwable)
    {
    }

    public function getThrowable(): Throwable
    {
        return $this->throwable;
    }
}
