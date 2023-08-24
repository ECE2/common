<?php

declare(strict_types=1);

namespace Ece2\Common\Middleware;

use App\JsonRpc\Service\SystemCompanyService;
use App\Model\Company;
use Ece2\Common\JsonRpc\Contract\SystemCompanyServiceInterface;
use Hyperf\Codec\Json;
use Hyperf\Collection\Arr;
use Hyperf\Di\Annotation\Inject;
use HyperfExtension\Jwt\Jwt;
use HyperfExtension\Jwt\JwtFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CompanyMiddleware implements MiddlewareInterface
{
    #[Inject]
    public SystemCompanyServiceInterface $companyService;

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $payload = [];
        try {
            // 固定从 jwt 取 company_id
            $token = '';
            $header = $request->getHeaderLine('Authorization');
            if ($header && preg_match('/' . 'Bearer' . '\s*(\S+)\b/i', $header, $matches)) {
                $token = $matches[1];
            }
            $payload = Json::decode(base64_decode(explode('.', $token)[1] ?? ''));
        } catch (\Exception $e) {
        }

        // 取公司
        if (!empty($companydId = $payload['company_id'] ?? null)) {
            company_set(is_base_system() ?
                Company::query()->find($companydId) :
                new \Ece2\Common\Model\Rpc\Model\Company($this->companyService->getByIds([$companydId])['data'][0] ?? []));
        }

        return $handler->handle($request);
    }
}
