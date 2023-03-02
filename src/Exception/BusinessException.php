<?php

declare(strict_types=1);

namespace Ece2\Common\Exception;

use App\Constants\ErrorCode;
use Ece2\Common\Exception\HttpException as BaseHttpException;
use Throwable;

class BusinessException extends BaseHttpException
{
    public function __construct(int $code = 0, string|array $message = null, int $statusCode = 400, Throwable $previous = null)
    {
        parent::__construct(
            $code,
            trans(ErrorCode::getMessage(
                $code,
                empty($message) || is_array($message) ? $message : ['error' => (string) $message]
            )),
            $statusCode,
            $previous
        );
    }
}
