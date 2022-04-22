<?php

declare(strict_types=1);

namespace Ece2\Common\Implement;

use Ece2\Common\Exception\TokenException;
use Ece2\Common\Interfaces\AuthenticationInterface;
use Ece2\Common\JsonRpc\Contract\SystemUserServiceInterface;
use Ece2\Common\Model\Rpc\Model\SystemUser;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Utils\Str;

/**
 * 子应用鉴权 (走 rpc 到系统基础判断).
 */
class SubAppAuthentication implements AuthenticationInterface
{
    public function check(string $token = '', string $scene = 'default')
    {
        $tokenFromHeader = container()->get(RequestInterface::class)->getHeader('Authorization');
        // 截取获得 token
        if (empty($token = Str::substr($tokenFromHeader[0] ?? '', Str::length('Bearer ')))) {
            throw new TokenException(t('jwt.no_token'));
        }

        // 获取身份信息然后写入上下文
        $user = container()->get(SystemUserServiceInterface::class)->getInfoByJwtToken($token, $scene);
        if (! ($user['success'] ?? false) || empty($user['data'])) { // TODO 统一处理返回是否成功
            throw new TokenException(t('jwt.no_token'));
        }
        $userInstance = new SystemUser($user['data']);
        identity_set(static fn () => $userInstance);

        return true;
    }
}
