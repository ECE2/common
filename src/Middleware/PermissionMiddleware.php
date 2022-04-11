<?php

declare(strict_types=1);

namespace Ece2\Common\Middleware;

use App\Exception\BusinessException;
use Ece2\Common\JsonRpc\Contract\AdministratorServiceInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Router\Dispatched;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Swoole\Http\Status;

class PermissionMiddleware implements MiddlewareInterface
{
    /**
     * @Inject
     */
    protected AdministratorServiceInterface $administratorService;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // 获取路由信息 (权限标注)
        /** @var Dispatched $dispatched */
        $dispatched = $request->getAttribute(Dispatched::class);
        $permission = $dispatched->handler->options['permission'] ?? [];
        if (empty($permission) || env('APP_ENV') === 'dev') {
            return $handler->handle($request);
        }

        // 获取当前用户拥有的菜单权限
        // currentAdmin() 用户信息, 通过 ValidateTokenMiddleware (db model)/RefreshTokenMiddleware (rpc model) 存储了用户上下文而来
        $adminPermission = array_column(currentAdmin()?->menusSameLevel(), 'permission_identity');
        if (array_intersect($permission, $adminPermission)) {
            return $handler->handle($request);
        }

        throw new BusinessException(403, '您无权访问', Status::FORBIDDEN);
    }
}
