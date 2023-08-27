<?php

declare(strict_types=1);

namespace Ece2\Common\Middleware;

use App\Model\Company;
use Ece2\Common\Model\Rpc\Model\Company as CompanyForRpc;
use Hyperf\Collection\Arr;
use Hyperf\Rpc\Context as RpcContext;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function Hyperf\Config\config;

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
        // 接收 rpc 的请求来时
        /** @var RpcContext $rc */
        $rc = $this->container->get(RpcContext::class);
        // 获取 rpc 的上下文里的用户数据
        if ($currentUser = $rc->get('current.user')) {
            // guard config
            $guardProvider = Arr::get(config('auth'), "guards_provider.{$currentUser['guard']}");

            if (is_base_system()) {
                if (empty($guardProvider['system_model'])) {
                    throw new \Exception('无 system_model, 请检查 guards_provider 相关配置');
                }
                identity_set(static fn () => new $guardProvider['system_model']($currentUser));
            } else {
                if (empty($guardProvider['rpc_model'])) {
                    throw new \Exception('无 system_model, 请检查 guards_provider 相关配置');
                }
                // 其他项目使用 rpc model 类
                // 由 system 项目在 Ece2\Common\Aspect\JsonRpcIdTransferAspect 写入 rpc 上下文
                identity_set(static fn () => new $guardProvider['rpc_model']($currentUser));
            }
        }

//        // 获取 rpc 的上下文里公司数据
//        if ($currentCompany = $rc->get('current.company')) {
//            company_set(is_base_system() ? new Company($currentCompany) : new CompanyForRpc($currentCompany));
//        }

        // 获取 rpc 的上下文里 useSuperAdmin 数据
        context_set('userSuperAdmin', $rc->get('userSuperAdmin'));

        return $handler->handle($request);
    }
}
