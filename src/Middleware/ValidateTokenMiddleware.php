<?php

declare(strict_types=1);

namespace Ece2\Common\Middleware;

use Ece2\Common\JsonRpc\Contract\AdministratorServiceInterface;
use Ece2\Common\Model\Rpc\Model\Administrator;
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

        // 保存当前管理员 上下文, 注意: 此处的示例为 rpc model
        Context::set('userResolver', static fn () => new Administrator($admin['data'] ?? []));

        return $handler->handle($request);
    }
}
