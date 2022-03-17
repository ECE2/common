<?php

namespace Ece2\Common\JsonRpc\Contract;

interface AdministratorServiceInterface
{
    public function getInfoByJwtToken(string $jwtToken);
}
