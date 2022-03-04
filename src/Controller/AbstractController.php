<?php

declare(strict_types=1);

namespace Ece2\HyperfCommon\Controller;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Logger\LoggerFactory;
use Hyperf\ServiceGovernance\IPReaderInterface;
use Hyperf\Utils\ApplicationContext;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

abstract class AbstractController
{
    /**
     * @Inject
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @Inject
     * @var RequestInterface
     */
    protected $request;

    /**
     * @Inject
     * @var ResponseInterface
     */
    protected $response;

    /**
     * 返回 json http response 结果.
     * 配合 ant design pro react 统一规范: (https://pro.ant.design/zh-CN/docs/request#%E7%BB%9F%E4%B8%80%E8%A7%84%E8%8C%83).
     * @param mixed $data 返回数据
     * @param bool $success 是否成功
     * @param int $errorCode 错误码
     * @param string $errorMessage 错误信息
     * @param int $showType 错误提示类型
     * @param string $traceId trace id
     * @param string $host host
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function jsonResponse(
        mixed $data = [],
        bool $success = true,
        int $errorCode = 0,
        string $errorMessage = '',
        int $showType = 0,
        string $traceId = '',
        string $host = ''
    ): \Psr\Http\Message\ResponseInterface {
        try {
            $host = $host ?: (ApplicationContext::getContainer()->get(IPReaderInterface::class))->read();
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            $host = '';
        }

        return $this->response->json([
            'success' => $success,
            'data' => $data ?: [],
            'errorCode' => $errorCode ?: 0,
            'errorMessage' => $errorMessage ?: '',
            'showType' => $showType ?: 0, // error display type： 0 silent; 1 message.warn; 2 message.error; 4 notification; 9 page
            'traceId' => $traceId ?: '',
            'host' => $host,
        ]);
    }
}
