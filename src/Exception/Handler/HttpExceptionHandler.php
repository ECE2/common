<?php

declare(strict_types=1);
/**
 * This file is part of api template.
 */
namespace Ece2\HyperfCommon\Exception\Handler;

use Ece2\HyperfCommon\Library\IPReader;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Exception\HttpException;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Utils\Codec\Json;
use Psr\Http\Message\ResponseInterface;
use Swoole\Http\Status;
use Throwable;

class HttpExceptionHandler extends ExceptionHandler
{
    public function __construct(protected StdoutLoggerInterface $logger)
    {
    }

    /**
     * @param HttpException $throwable
     * @return ResponseInterface
     */
    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        $this->logger->error(sprintf('%s[%s] in %s', $throwable->getMessage(), $throwable->getLine(), $throwable->getFile()));

        $statusCode = $throwable->getCode() ?: Status::INTERNAL_SERVER_ERROR;
        if ($throwable instanceof HttpException) {
            $statusCode = $throwable->getStatusCode();
        }

        $this->stopPropagation(); // 停止传到下个错误处理

        return $response
            ->withStatus($statusCode)
            ->withAddedHeader('content-type', 'application/json; charset=utf-8')
            ->withBody(new SwooleStream(Json::encode([
                // 配合前端统一规范
                // https://pro.ant.design/zh-CN/docs/request#%E7%BB%9F%E4%B8%80%E8%A7%84%E8%8C%83
                'success' => false,
                'data' => [],
                'errorCode' => $throwable->getCode(),
                'errorMessage' => $throwable->getMessage(),
                'showType' => 2, // error display type： 0 silent; 1 message.warn; 2 message.error; 4 notification; 9 page
                'traceId' => '', // TODO
                'host' => (new IPReader())->read(),
            ])));
    }

    public function isValid(Throwable $throwable): bool
    {
        return $throwable instanceof HttpException;
    }
}
