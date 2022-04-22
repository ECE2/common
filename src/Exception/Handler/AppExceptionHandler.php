<?php

declare(strict_types=1);

namespace Ece2\Common\Exception\Handler;

use Ece2\Common\Library\Log;
use Ece2\Common\Library\TraceId;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Logger\LoggerFactory;
use Hyperf\ServiceGovernance\IPReaderInterface;
use Hyperf\Utils\Codec\Json;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Hyperf\Logger\Logger;
use Swoole\Http\Status;
use Throwable;

class AppExceptionHandler extends ExceptionHandler
{
    /**
     * @var StdoutLoggerInterface
     */
    protected StdoutLoggerInterface $console;

    public function __construct()
    {
        $this->console = console();
    }

    public function handle(Throwable $throwable, ResponseInterface $response): ResponseInterface
    {
        // TODO ...
        $this->console->error(sprintf('%s[%s] in %s', $throwable->getMessage(), $throwable->getLine(), $throwable->getFile()));
        $this->console->error($throwable->getTraceAsString());
        Log::error(sprintf('%s[%s] in %s', $throwable->getMessage(), $throwable->getLine(), $throwable->getFile()));

        return $response
            ->withAddedHeader('content-type', 'application/json; charset=utf-8')
            ->withStatus(Status::INTERNAL_SERVER_ERROR)
            ->withBody(new SwooleStream(Json::encode([
                'success' => false,
                'code' => 500,
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
