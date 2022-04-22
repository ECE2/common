<?php

declare(strict_types=1);

namespace Ece2\Common\Interfaces;

/**
 * 用户鉴权接口.
 */
interface AuthenticationInterface
{
    /**
     * 验证
     * @param string $token
     * @param string $scene
     * @return mixed
     */
    public function check(string $token = '', string $scene = 'default');
}
