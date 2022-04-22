<?php

declare(strict_types=1);

namespace Ece2\Common;

use Ece2\Common\Library\TraceId;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Response as BaseResponse;
use Psr\Http\Message\ResponseInterface;

class Response extends BaseResponse
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function success(string $message = null, array|object $data = [], int $code = 200): ResponseInterface
    {
        return $this
            ->getResponse()
            ->withAddedHeader('content-type', 'application/json; charset=utf-8')
            ->withBody(new SwooleStream(
                $this->toJson([
                    // TODO 可以改成类
                    'success' => true,
                    'code' => $code,
                    'message' => $message ?: t('response_success'),
                    'data' => $data,
                    'traceId' => TraceId::get(),
                    'host' => host(),
                ])
            ));
    }

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function error(string $message = '', int $code = 500, array $data = []): ResponseInterface
    {
        return $this
            ->getResponse()
            ->withAddedHeader('content-type', 'application/json; charset=utf-8')
            ->withBody(new SwooleStream(
                $this->toJson([
                    'success' => false,
                    'code' => $code,
                    'message' => $message ?: t('response_error'),
                    'data' => $data,
                    'traceId' => TraceId::get(),
                    'host' => host(),
                ])
            ));
    }

    /**
     * 向浏览器输出图片.
     */
    public function responseImage(string $image, string $type = 'image/png'): ResponseInterface
    {
        return $this
            ->getResponse()
            ->withAddedHeader('content-type', $type)
            ->withBody(new SwooleStream($image));
    }

    public function getResponse(): ResponseInterface
    {
        return parent::getResponse();
    }
}
