<?php

namespace Ece2\Common\Abstracts;

use Ece2\Common\Traits\JsonRpcTrait;

abstract class AbstractJsonRpcService
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @return array
     */
    public function success(mixed $data = [], ?string $message = null, int $code = 200)
    {
        return [
            'success' => true,
            'code' => $code,
            'message' => $message ?: t('response_success'),
            'data' => $data,
        ];
    }

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @return array
     */
    public function error(string $message = '', int $code = 500, array $data = [])
    {
        return [
            'success' => false,
            'code' => $code,
            'message' => $message ?: t('response_error'),
            'data' => $data,
        ];
    }
}
