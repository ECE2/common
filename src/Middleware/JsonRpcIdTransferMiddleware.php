<?php

declare(strict_types=1);

namespace Ece2\Common\Middleware;

use App\Model\Administrator as BaseDBAdministrator;
use Ece2\Common\Model\Rpc\Model\Administrator as RpcAdministrator;
use Hyperf\Context\Context;
use Hyperf\Rpc\Context as RpcContext;
use Hyperf\Utils\Composer;
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
            if (Composer::getJsonContent()->get('name') === 'base/admin') { // admin 项目使用自己的 db modal 类
                Context::set('currentAdmin', static fn () => new BaseDBAdministrator($currentAdmin));
            } else { // 其他项目使用 rpc model 类
                Context::set('currentAdmin', static fn () => new RpcAdministrator($currentAdmin));
            }
        }

        return $handler->handle($request);
    }
}
