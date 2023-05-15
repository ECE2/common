<?php

declare(strict_types=1);

namespace Ece2\Common\Implement;

use Ece2\Common\Exception\TokenException;
use Ece2\Common\Interfaces\AuthenticationInterface;
use Ece2\Common\JsonRpc\Contract\SystemUserServiceInterface;
use Ece2\Common\Model\Rpc\Model\User;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Utils\Str;

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

        // 获取身份信息然后写入上下文
        $user = container()->get(SystemUserServiceInterface::class)->getInfoByJwtToken($token, $guard);
        if (! ($user['success'] ?? false) || empty($user['data'])) {
            throw new TokenException(t('jwt.no_token'));
        }
        $userInstance = new User($user['data']);
        identity_set(static fn () => $userInstance);

        return true;
    }
}
