<?php

declare(strict_types=1);

namespace Ece2\Common\Middleware;

use App\JsonRpc\Service\SystemCompanyService;
use App\Model\Company;
use Ece2\Common\JsonRpc\Contract\SystemCompanyServiceInterface;
use Hyperf\Collection\Arr;
use Hyperf\Di\Annotation\Inject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CompanyMiddleware implements MiddlewareInterface
{
    #[Inject]
    public SystemCompanyServiceInterface $companyService;

    /**
     * 根据请求 host (c1232.test.qiang-ji.com) 的 c1232 子域名获取到对应的 company 信息, 优先使用提交的 sub_domain 参数, 并写入上下文
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $subDomain = Arr::first(explode('.', Arr::first($request->getHeader('host'))));

        // 允许 dev 环境传 sub_domain
        if (\Hyperf\Config\config('app_env') === 'dev') {
            $subDomain = array_merge($request->getParsedBody(), $request->getQueryParams())['sub_domain'] ?? \Hyperf\Support\env('SUB_DOMAIN') ?? $subDomain;
            dump('当前开发环境, 传入的 sub_domain 为: ' . $subDomain);
        }

        if ($subDomain !== null && !is_numeric($subDomain)) {
            if (is_base_system()) {
                company_set(Company::query()->where('sub_domain', $subDomain)->first());
            } else {
                company_set(new \Ece2\Common\Model\Rpc\Model\Company($this->companyService->getCompanyBySubDomain($subDomain)['data'] ?? []));
            }
        }

        return $handler->handle($request);
    }
}
