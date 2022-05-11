<?php

declare(strict_types=1);

namespace Ece2\Common\Abstracts;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Request;
use Hyperf\HttpServer\Response;
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
            return $this->response->success($msgOrData, $data, $code);
        }
        if (is_array($msgOrData) || is_object($msgOrData)) {
            return $this->response->success(null, $msgOrData, $code);
        }
        return $this->response->success(null, $data, $code);
    }

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function error(string $message = '', int $code = 500, array $data = [])
    {
        return $this->response->error($message, $code, $data);
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
}
