<?php

declare(strict_types=1);

namespace Ece2\Common\Event;

class Operation
{
    public function __construct(protected array $requestInfo)
    {
    }

    public function getRequestInfo(): array
    {
        return $this->requestInfo;
    }
}
