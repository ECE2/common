<?php

declare(strict_types=1);

namespace Ece2\Common\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CommonMiddleware implements MiddlewareInterface
{
    /**
     * 公共的一些处理
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // 使用超管身份, 具体还需要根据用户身份一起判断
        context_set('useSuperAdmin', $request->getHeaderLine('x-use-superadmin') === 'true');

        $result = $handler->handle($request);

        return $result;
    }
}
