<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Ece2\Common\Exception;

use App\Constants\ErrorCode;
use Hyperf\Server\Exception\ServerException;
use Throwable;

class AppException extends ServerException
{

    public function __construct(int $code = 0, string $message = null, Throwable $previous = null)
    {
        if (is_null($message)) {
            $message = \Hyperf\Translation\trans(ErrorCode::getMessage($code));
        } else {
            $message = \Hyperf\Translation\trans(ErrorCode::getMessage($code), array('error' => $message)) ?: $message;
        }

        parent::__construct($message, $code, $previous);
    }
}
