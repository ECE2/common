<?php

declare(strict_types=1);

namespace Ece2\Common\Exception\Handler;

use Ece2\Common\Helper\Code;
use Ece2\Common\Library\TraceId;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Utils\Codec\Json;
use Ece2\Common\Exception\TokenException;
use Psr\Http\Message\ResponseInterface;
use Swoole\Http\Status;
use Throwable;

class TokenExceptionHandler extends ExceptionHandler
{
    public function handle(Throwable $throwable, ResponseInterface $response): ResponseInterface
    {
        $this->stopPropagation();

        return $response
            ->withAddedHeader('content-type', 'application/json; charset=utf-8')
            ->withStatus(Status::UNAUTHORIZED)
            ->withBody(new SwooleStream(Json::encode([
                'success' => false,
                'message' => $throwable->getMessage(),
                'code'    => Code::TOKEN_EXPIRED,
                'data' => [],
                'traceId' => TraceId::get(),
                'host' => host(),
            ])));
    }

    public function isValid(Throwable $throwable): bool
    {
        return $throwable instanceof TokenException;
    }
}
