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

namespace Ece2\Common\Exception\Handler;

use Ece2\Common\Exception\AppException;
use Ece2\Common\Library\TraceId;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Utils\ApplicationContext;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class AppExceptionHandler extends ExceptionHandler
{

    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        $code = $throwable->getCode();
        $http_status = $code;
        if ($code > 999) {
            $http_status = substr((string) $code, 0, 3);
        }

        $data = array(
            'success' => false,
            'code' => $code,
            'message' => $throwable->getMessage(),
            'data' => [],
            'traceId' => TraceId::get(),
            'host' => host(),
        );

//        $exception_data = Context::get('exception_data', null);
//        if ($exception_data != null) {
//            $data['result'] = $exception_data;
//        }

        // 阻止异常冒泡
        $this->stopPropagation();

        return $response->withStatus($http_status)
            ->withHeader('Content-type', 'text/json; charset=utf-8')
            ->withHeader('Access-Control-Allow-Credentials', 'true')
            ->withHeader('Access-Control-Allow-Origin', ApplicationContext::getContainer()->get(RequestInterface::class)->header('origin'))
            ->withHeader('Access-Control-Allow-Headers', 'Origin, X-Requested-With, X_Requested_With, Content-Type, Accept')
            ->withHeader('Access-Control-Allow-Methods', '*')
            ->withBody(new SwooleStream(json_encode($data, JSON_UNESCAPED_UNICODE)));
    }

    public function isValid(Throwable $throwable): bool
    {
        return $throwable instanceof AppException;
    }
}
