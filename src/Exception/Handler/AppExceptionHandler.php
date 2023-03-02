<?php

declare(strict_types=1);

namespace Ece2\Common\Exception\Handler;

use Ece2\Common\Library\Log;
use Ece2\Common\Library\TraceId;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Exception\HttpException;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\ServiceGovernance\IPReaderInterface;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Codec\Json;
use Psr\Http\Message\ResponseInterface;
use Swoole\Http\Status;
use Throwable;

class AppExceptionHandler extends ExceptionHandler
{
    /**
     * @param HttpException $throwable
     * @return ResponseInterface
     */
    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        Log::error(sprintf('%s[%s] in %s', $throwable->getMessage(), $throwable->getLine(), $throwable->getFile()));
        config('app_env') === 'dev' && Log::error($throwable->getTraceAsString());

        $statusCode = $throwable->getCode() ?: Status::INTERNAL_SERVER_ERROR;
        if ($throwable instanceof HttpException) {
            $statusCode = $throwable->getStatusCode();
        }

        return $response
            ->withStatus($statusCode)
            ->withAddedHeader('content-type', 'application/json; charset=utf-8')
            ->withBody(new SwooleStream(Json::encode([
                'success' => false,
                'code' => $throwable->getCode(),
                'message' => $throwable->getMessage(),
                'data' => [],
                'traceId' => TraceId::get(),
                'host' => host(),
            ])));
    }

    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}
