<?php

declare(strict_types=1);

namespace Ece2\Common\Exception;

use Hyperf\HttpMessage\Exception\HttpException as BaseHttpException;
use Swoole\Http\Status;
use Throwable;

class HttpException extends BaseHttpException
{
    public function __construct(int $code = 0, string $message = null, int $statusCode = Status::BAD_REQUEST, Throwable $previous = null)
    {
        parent::__construct($statusCode, $message, $code, $previous);
    }
}
