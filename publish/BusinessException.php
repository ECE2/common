<?php

declare(strict_types=1);

namespace App\Exception;

//use App\Constants\ErrorCode;
use Ece2\HyperfCommon\Exception\HttpException as BaseHttpException;
use Throwable;

class BusinessException extends BaseHttpException
{
    public function __construct(int $code = 0, string $message = null, int $statusCode = 400, Throwable $previous = null)
    {
//        if (is_null($message)) {
//            $message = ErrorCode::getMessage($code);
//        }

        parent::__construct($code, $message, $statusCode, $previous);
    }
}
