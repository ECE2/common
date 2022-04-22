<?php

declare(strict_types = 1);

namespace Ece2\Common\Event;

class UserLoginAfter
{
    public bool $loginStatus = true;

    public string $message;

    public string $token;

    public function __construct(public array $userinfo)
    {
    }
}
