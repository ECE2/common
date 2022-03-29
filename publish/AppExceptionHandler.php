<?php

declare(strict_types=1);

namespace App\Exception\Handler;

use Ece2\Common\Library\TraceId;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Exception\HttpException;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Logger\LoggerFactory;
use Hyperf\ServiceGovernance\IPReaderInterface;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Codec\Json;
use Psr\Http\Message\ResponseInterface;
use Swoole\Http\Status;
use Throwable;

class AppExceptionHandler extends ExceptionHandler
{
    protected $logger;

    public function __construct(protected LoggerFactory $loggerFactory)
    {
        $this->logger = $this->loggerFactory->get('log');
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
                'traceId' => TraceId::get(),
                'host' => ApplicationContext::getContainer()->get(IPReaderInterface::class)->read(),
            ])));
    }

    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}
