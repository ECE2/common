<?php

declare(strict_types=1);

namespace Ece2\Common\Aspect;

use Ece2\Common\Annotation\Auth;
use Ece2\Common\Exception\TokenException;
use Ece2\Common\Interfaces\AuthenticationInterface;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Di\Exception\Exception;

#[Aspect]
class AuthAspect extends AbstractAspect
{
    public array $annotations = [
        Auth::class,
    ];

    /**
     * @var int aop 优先级
     */
    public ?int $priority = 100;

    public function __construct(protected AuthenticationInterface $authentication)
    {
    }

    /**
     * @throws Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @return mixed
     */
    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if ($this->authentication->check()) {
            return $proceedingJoinPoint->process();
        }

        throw new TokenException(t('jwt.validate_fail'));
    }
}
