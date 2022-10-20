<?php

declare(strict_types=1);

namespace Ece2\Common\Aspect;

use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Ece2\Common\Amqp\Event\AfterConsume;
use Ece2\Common\Amqp\Event\BeforeConsume;
use Ece2\Common\Amqp\Event\FailToConsume;

#[Aspect]
class ConsumeAspect extends AbstractAspect
{
    public array $classes = [
        'Hyperf\Amqp\Message\ConsumerMessage::consumeMessage'
    ];

    /**
     * @param ProceedingJoinPoint $proceedingJoinPoint
     * @return mixed|null
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $data = $proceedingJoinPoint->getArguments()[0];
        $message = $proceedingJoinPoint->getArguments()[1];

        try {
            event(new BeforeConsume($message, $data));
            $result = $proceedingJoinPoint->process();
            event(new AfterConsume($message, $data, $result));

            return $result;
        } catch (\Throwable $e) {
            event(new FailToConsume($message, $data, $e));
            return null;
        }
    }
}
