<?php

declare(strict_types=1);

namespace Ece2\Common\Middleware;

use App\Model\User as BaseDBAdministrator;
use Ece2\Common\Model\Rpc\Model\User as RpcAdministrator;
use Hyperf\Rpc\Context as RpcContext;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Json Rpc 传递的当前用户信息
 */
class JsonRpcIdTransferMiddleware implements MiddlewareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var RpcContext $rc */
        $rc = $this->container->get(RpcContext::class);
        if ($currentAdmin = $rc->get('current.admin')) {
            if (is_base_system()) {
                identity_set(static fn () => new BaseDBAdministrator($currentAdmin));
            } else {
                // 其他项目使用 rpc model 类
                // 由 system 项目在 Ece2\Common\Aspect\JsonRpcIdTransferAspect 写入 rpc 上下文
                identity_set(static fn () => new RpcAdministrator($currentAdmin));
            }
        }

        return $handler->handle($request);
    }
}
