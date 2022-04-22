<?php

namespace Ece2\Common\Event;

use Psr\Http\Message\ResponseInterface;

class ApiAfter
{
    public function __construct(protected ?array $apiData, protected ResponseInterface $result)
    {
    }

    public function getApiData(): ?array
    {
        return $this->apiData;
    }

    public function getResult(): ResponseInterface
    {
        return $this->result;
    }
}
