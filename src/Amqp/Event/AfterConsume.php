<?php

declare(strict_types=1);

namespace Ece2\Common\Amqp\Event;

use Hyperf\Amqp\Message\ConsumerMessageInterface;

class AfterConsume
{
    public function __construct(
        public ConsumerMessageInterface $message,
        public                          $data,
        public                          $result
    )
    {
    }
}
