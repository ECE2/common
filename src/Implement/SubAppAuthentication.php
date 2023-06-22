<?php

declare(strict_types=1);

namespace Ece2\Common\Implement;

use Ece2\Common\Exception\TokenException;
use Ece2\Common\Interfaces\AuthenticationInterface;
use Hyperf\Collection\Arr;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Stringable\Str;

use function Hyperf\Config\config;

/**
 * 子应用鉴权 (走 rpc 到系统基础判断).
 */
class SubAppAuthentication implements AuthenticationInterface
{
    public function check(string $token = '', string $guard = 'api')
    {
        $request = container()->get(RequestInterface::class);

        // header 截取获得获取, 7 => Str::length('Bearer ')
        $token = Str::substr($request->getHeaderLine('Authorization'), 7) ?: $request->input('token');
        if (empty($token)) {
            throw new TokenException(t('jwt.no_token'));
        }

        // 根据 common 公共组件包配置的 guards 相关获取 guard 对应的 rpc interface, 拿 token 换用户数据
        $guardProvider = Arr::get(config('auth'), "guards_provider.$guard");
        if (empty($rpcInterfaceName = $guardProvider['rpc_interface'] ?? '')) {
            throw new TokenException('未找到 guard 对应的 interface, 请检查 common 包下的 JsonRpc/Contract/ 是否配置正确');
        }
        $user = container()->get($rpcInterfaceName)->getInfoByJwtToken($token, $guard);
        if (! ($user['success'] ?? false) || empty($user['data'])) {
            throw new TokenException(t('jwt.no_token'));
        }

        // 然后写入上下文
        if (empty($rpcModelName = $guardProvider['rpc_model'] ?? '')) {
            throw new TokenException('未找到 guard 对应的 rpc model, 请检查 common 包下的 Model/Rpc/Model/ 是否配置正确');
        }
        identity_set(fn () => new ($rpcModelName)(array_merge($user['data'], ['guard' => $guard])));

        return true;
    }
}
