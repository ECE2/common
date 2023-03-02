<?php

declare(strict_types=1);

namespace Ece2\Common\Abstracts;

use Ece2\Common\Library\TraceId;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Request;
use Hyperf\HttpServer\Response;
use Hyperf\Utils\Codec\Json;
use Psr\Container\ContainerInterface;

abstract class AbstractController
{
    #[Inject]
    protected Request $request;

    #[Inject]
    protected Response $response;

    #[Inject]
    protected ContainerInterface $container;

    /**
     * @param array $data
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function success(mixed $msgOrData = '', array|object $data = [], int $code = 200)
    {
        if (is_string($msgOrData) || is_null($msgOrData)) {
            return $this->_success($msgOrData, $data, $code);
        }

        if (is_array($msgOrData) || is_object($msgOrData)) {
            return $this->_success(null, $msgOrData, $code);
        }

        return $this->_success(null, $data, $code);
    }

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function error(string $message = '', int $code = 500, array $data = [])
    {
        return $this->response->_error($message, $code, $data);
    }

    /**
     * 跳转.
     */
    public function redirect(string $toUrl, int $status = 302, string $schema = 'http')
    {
        return $this->response->redirect($toUrl, $status, $schema);
    }

    /**
     * 下载文件.
     */
    public function _download(string $filePath, string $name = '')
    {
        return $this->response->download($filePath, $name);
    }

    /**
     * 返回成功
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function _success(string $message = null, array|object $data = [], int $code = 200)
    {
        return $this
            ->response
            ->withAddedHeader('content-type', 'application/json; charset=utf-8')
            ->withBody(new SwooleStream(Json::encode([
                'success' => true,
                'code' => $code,
                'message' => $message ?: t('response_success'),
                'data' => $data,
                'traceId' => TraceId::get(),
                'host' => host(),
            ])));
    }

    /**
     * 返回失败
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function _error(string $message = '', int $code = 500, array $data = [])
    {
        return $this
            ->response
            ->withAddedHeader('content-type', 'application/json; charset=utf-8')
            ->withBody(new SwooleStream(Json::encode([
                'success' => false,
                'code' => $code,
                'message' => $message ?: t('response_error'),
                'data' => $data,
                'traceId' => TraceId::get(),
                'host' => host(),
            ])));
    }
}
