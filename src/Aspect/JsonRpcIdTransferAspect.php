<?php

declare(strict_types=1);

namespace Ece2\Common\Aspect;

use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Rpc\Context;
use Hyperf\RpcClient\AbstractServiceClient;
use Psr\Container\ContainerInterface;

/**
 * @Aspect(
 *     classes={
 *         "Hyperf\RpcClient\AbstractServiceClient::__generateRpcPath"
 *     }
 * )
 */
class JsonRpcIdTransferAspect extends AbstractAspect
{
    /**
     * @var Context
     */
    private mixed $context;

    public function __construct(ContainerInterface $container)
    {
        $this->context = $container->get(Context::class);
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if ($currentAdmin = currentAdmin()) {
            $this->context->set('current.admin', $currentAdmin);
        }

        return $proceedingJoinPoint->process();
    }
}
