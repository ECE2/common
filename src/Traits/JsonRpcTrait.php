<?php

declare(strict_types=1);

namespace Ece2\Common\Traits;

/**
 * JsonRpc 返回.
 * TODO 待优化, 返回可做成统一类.
 */
trait JsonRpcTrait
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @return array
     */
    public function success(array|object $data = [], ?string $message = null, int $code = 200)
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
