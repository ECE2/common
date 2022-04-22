<?php

declare(strict_types=1);

namespace Ece2\Common\Exception\Handler;

use Ece2\Common\Library\TraceId;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Utils\Codec\Json;
use Ece2\Common\Exception\NoPermissionException;
use Ece2\Common\Helper\Code;
use Psr\Http\Message\ResponseInterface;
use Swoole\Http\Status;
use Throwable;

class NoPermissionExceptionHandler extends ExceptionHandler
{
    public function handle(Throwable $throwable, ResponseInterface $response): ResponseInterface
    {
        $this->stopPropagation();

        return $response
            ->withAddedHeader('content-type', 'application/json; charset=utf-8')
            ->withStatus(Status::FORBIDDEN)
            ->withBody(new SwooleStream(Json::encode([
                'success' => false,
                'code'    => Code::NO_PERMISSION,
                'message' => $throwable->getMessage(),
                'data' => [],
                'traceId' => TraceId::get(),
                'host' => host(),
            ])));
    }

    public function isValid(Throwable $throwable): bool
    {
        return $throwable instanceof NoPermissionException;
    }
}
