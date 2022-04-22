<?php

declare(strict_types = 1);

namespace Ece2\Common\Event;

class UserLoginBefore
{
    public function __construct(public array $inputData)
    {
    }
}
