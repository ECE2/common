<?php

namespace Ece2\Common\JsonRpc;

class JsonRpcServices
{
    public function __invoke()
    {
        return [
            'AdministratorService' => \Ece2\Common\JsonRpc\Contract\AdministratorServiceInterface::class,
        ];
    }
}
