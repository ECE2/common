<?php

declare(strict_types=1);

namespace Ece2\Common\Middleware;

use Hyperf\Utils\Context;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CorsMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (config('app_env') === 'dev') {
            $response = Context::get(ResponseInterface::class)
                ?->withHeader('Access-Control-Allow-Origin', '*')
                ->withHeader('Access-Control-Allow-Credentials', 'true')
                ->withHeader('Access-Control-Allow-Headers', 'DNT,Keep-Alive,User-Agent,Cache-Control,Content-Type,Authorization');

            Context::set(ResponseInterface::class, $response);
            if ($request->getMethod() === 'OPTIONS') {
                return $response;
            }
        }

        return $handler->handle($request);
    }
}
