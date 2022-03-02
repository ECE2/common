<?php

declare(strict_types=1);

namespace Ece2\HyperfCommon;

use Ece2\HyperfCommon\JsonRpc\JsonRpcServices;
use Ece2\HyperfCommon\Library\IPReader;

class ConfigProvider
{
    public function __invoke(): array
    {
        // 文档: https://hyperf.wiki/2.2/#/zh-cn/json-rpc
        $consumersRegistry = [
            'protocol' => 'nacos',
            'address' => sprintf('http://%s:%s', env('NACOS_HOST'), env('NACOS_PORT')),
        ];

        return [
            'dependencies' => [
                // 替换原有的 ip 获取, 允许使用配置了的服务发现地址
                \Hyperf\ServiceGovernance\IPReaderInterface::class => IPReader::class,
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            'services' => [
                'consumers' => value(function () use ($consumersRegistry) {
                    $consumers = [];
                    // 这里示例自动创建代理消费者类的配置形式，顾存在 name 和 service 两个配置项，这里的做法不是唯一的，仅说明可以通过 PHP 代码来生成配置
                    // 下面的 FooServiceInterface 和 BarServiceInterface 仅示例多服务，并不是在文档示例中真实存在的
                    $services = new JsonRpcServices();
                    foreach ($services() as $name => $interface) {
                        $consumers[] = [
                            'name' => $name,
                            'service' => $interface,
                            'registry' => $consumersRegistry,
                        ];
                    }
                    return $consumers;
                }),
            ],
            'exceptions' => [
                'handler' => [
                    'http' => [
                        \Ece2\HyperfCommon\Exception\Handler\HttpExceptionHandler::class,
                    ],
                ]
            ],
            'publish' => [
                [
                    'id' => 'AbstractController',
                    'description' => 'replace AbstractController',
                    'source' => __DIR__ . '/../publish/AbstractController.php',
                    'destination' => BASE_PATH . '/app/Controller/AbstractController.php',
                ],
                [
                    'id' => 'BusinessException',
                    'description' => 'replace BusinessException',
                    'source' => __DIR__ . '/../publish/BusinessException.php',
                    'destination' => BASE_PATH . '/app/Exception/BusinessException.php',
                ],
                [
                    'id' => 'Model',
                    'description' => 'replace Model',
                    'source' => __DIR__ . '/../publish/Model.php',
                    'destination' => BASE_PATH . '/app/Model/Model.php',
                ],
                [
                    'id' => 'exceptions',
                    'description' => 'replace exceptions config',
                    'source' => __DIR__ . '/../publish/exceptions.php',
                    'destination' => BASE_PATH . '/config/autoload/exceptions.php',
                ],
                [
                    'id' => 'services',
                    'description' => 'replace services config',
                    'source' => __DIR__ . '/../publish/services.php',
                    'destination' => BASE_PATH . '/config/autoload/services.php',
                ],
                [
                    'id' => 'nacos',
                    'description' => 'replace nacos config',
                    'source' => __DIR__ . '/../publish/nacos.php',
                    'destination' => BASE_PATH . '/config/autoload/nacos.php',
                ],
            ]
        ];
    }
}
