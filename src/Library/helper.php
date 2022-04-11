<?php

declare(strict_types=1);

use Hyperf\Database\Model\Model;
use Hyperf\Utils\Context;

/**
 * 获取上下文内的当前用户信息.
 * @return array|mixed|Model
 */
function currentAdmin()
{
    $admin = null;
    try {
        if (($userResolver = Context::get('currentAdmin')) && is_callable($userResolver)) {
            $admin = $userResolver();
        }
    } catch (Exception $e) {
    }

    return $admin;
}
