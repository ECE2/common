<?php

declare(strict_types=1);

namespace App\Middleware\Auth;

use Ece2\Common\JsonRpc\Contract\AdministratorServiceInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpMessage\Exception\HttpException;
use Hyperf\Utils\Context;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ValidateTokenMiddleware implements MiddlewareInterface
{
    protected $header = 'authorization';

    protected $prefix = 'bearer';

    /**
     * @Inject
     * @var AdministratorServiceInterface
     */
    protected AdministratorServiceInterface $administratorService;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $header = $request->getHeaderLine($this->header);
        preg_match('/' . $this->prefix . '\s*(\S+)\b/i', $header, $matches);
        if (empty($matches[1] ?? '')) {
            throw new HttpException(401, $admin['errorMessage'] ?? null);
        }

        $admin = $this->administratorService->getInfoByJwtToken($matches[1] ?? '');
        if (! ($admin['success'] ?? false)) {
            throw new HttpException(401, $admin['errorMessage'] ?? null);
        }

        Context::set('userResolver', static fn () => $admin['data'] ?? []); // 保存当前管理员 上下文

        return $handler->handle($request);
    }
}
