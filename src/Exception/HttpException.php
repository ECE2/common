<?php

declare(strict_types=1);

namespace Ece2\HyperfCommon\Exception;

use Hyperf\HttpMessage\Exception\HttpException as BaseHttpException;
use Throwable;

class HttpException extends BaseHttpException
{
    public function __construct(int $code = 0, string $message = null, int $statusCode = 400, Throwable $previous = null)
    {
        parent::__construct($statusCode, $message, $code, $previous);
    }
}
