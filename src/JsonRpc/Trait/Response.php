<?php

namespace Ece2\HyperfCommon\JsonRpc\Trait;

use Hyperf\ServiceGovernance\IPReaderInterface;
use Hyperf\Utils\ApplicationContext;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

trait Response
{
    /**
     * copy from src/Controller/AbstractController
     * 这里和 http api 的规范统一
     * 返回 json http response 结果.
     * 配合 ant design pro react 统一规范: (https://pro.ant.design/zh-CN/docs/request#%E7%BB%9F%E4%B8%80%E8%A7%84%E8%8C%83).
     * @param mixed $data 返回数据
     * @param bool $success 是否成功
     * @param int $errorCode 错误码
     * @param string $errorMessage 错误信息
     * @param int $showType 错误提示类型
     * @param string $traceId trace id
     * @param string $host host
     */
    protected function jsonResponse(
        mixed $data = [],
        bool $success = true,
        int $errorCode = 0,
        string $errorMessage = '',
        int $showType = 0,
        string $traceId = '',
        string $host = ''
    ) {
        try {
            $host = $host ?: (ApplicationContext::getContainer()->get(IPReaderInterface::class))->read();
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            $host = '';
        }

        return [
            'success' => $success,
            'data' => $data ?: [],
            'errorCode' => $errorCode ?: 0,
            'errorMessage' => $errorMessage ?: '',
            'showType' => $showType ?: 0, // error display type： 0 silent; 1 message.warn; 2 message.error; 4 notification; 9 page
            'traceId' => $traceId ?: '',
            'host' => $host,
        ];
    }

}
