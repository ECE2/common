<?php

declare(strict_types=1);

namespace Ece2\Common\Aspect;

use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Rpc\Context;
use Psr\Container\ContainerInterface;

/**
 * JsonRpc 当前用户信息传递
 */
#[Aspect]
class JsonRpcIdTransferAspect extends AbstractAspect
{
    public array $classes = [
        'Hyperf\RpcClient\AbstractServiceClient::__generateRpcPath'
    ];

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
        // 从当前上下文获取用户信息, 写入 json rpc 上下文
        if ($currentAdmin = identity()) {
            $this->context->set('current.user', $currentAdmin);
        }

        return $proceedingJoinPoint->process();
    }
}
