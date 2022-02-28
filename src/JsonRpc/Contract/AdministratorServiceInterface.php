<?php

namespace Ece2\HyperfCommon\JsonRpc\Contract;

interface AdministratorServiceInterface
{
    public function getInfoByJwtToken(string $jwtToken);
}
