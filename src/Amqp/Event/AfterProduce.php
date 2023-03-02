<?php

declare(strict_types=1);

namespace Ece2\Common\Amqp\Event;

use Hyperf\Amqp\Message\ProducerMessageInterface;

class AfterProduce
{
    public function __construct(public ProducerMessageInterface $producer)
    {
    }
}
