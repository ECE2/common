<?php

namespace Ece2\HyperfCommon\JsonRpc;

class JsonRpcServices
{
    public function __invoke()
    {
        return [
            'AdministratorService' => \Ece2\HyperfCommon\JsonRpc\Contract\AdministratorServiceInterface::class,
        ];
    }
}
