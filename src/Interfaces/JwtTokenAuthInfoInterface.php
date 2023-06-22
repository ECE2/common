<?php

namespace Ece2\Common\Interfaces;

interface JwtTokenAuthInfoInterface
{
    /** 根据 token 获取用户信息 */
    public function getInfoByJwtToken(string $jwtToken, string $scene = '');
}
