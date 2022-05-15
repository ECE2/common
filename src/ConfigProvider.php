<?php

declare(strict_types=1);

namespace Ece2\Common;

use Hyperf\Database\Schema\Blueprint;
use Hyperf\Paginator\LengthAwarePaginator;
use Hyperf\Paginator\Paginator;
use Hyperf\ServiceGovernance\IPReader;

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
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                    // 框架的加载机制, 读取 composer.lock 按照字幕顺序, 这个组件包 dependencies 修改框架类会被覆盖掉, 导致替换的方式达不到修改框架类的情况, 这里修改框架类用 class_map 替代
                    'class_map' => [
                        // 替换原有的 ip 获取, 允许使用配置了的服务发现地址
                        IPReader::class => __DIR__ . '/../class_map/IPReader.php',
                        // migation 增加操作人更新人方法
                        Blueprint::class => __DIR__ . '/../class_map/Blueprint.php',
                        // 替换 Request 增加自定义函数
                        \Hyperf\HttpServer\Request::class => __DIR__ . '/../class_map/Request.php',
                        // 替换 Response 增加自定义函数
                        \Hyperf\HttpServer\Response::class => __DIR__ . '/../class_map/Response.php',
                        // 替换 Collection 增加自定义函数
                        \Hyperf\Database\Model\Collection::class => __DIR__ . '/../class_map/Collection.php',
                    ],
                    'ignore_annotations' => [
                        'required',
                    ],
                ],
            ],
            'services' => [
                'consumers' => value(function () use ($consumersRegistry) {
                    $consumers = [];
                    // 这里示例自动创建代理消费者类的配置形式，顾存在 name 和 service 两个配置项，这里的做法不是唯一的，仅说明可以通过 PHP 代码来生成配置
                    // 下面的 FooServiceInterface 和 BarServiceInterface 仅示例多服务，并不是在文档示例中真实存在的
                    $services = new \Ece2\Common\JsonRpc\JsonRpcServices();
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
            'middlewares' => [
                'http' => [
                    \Ece2\Common\Middleware\CorsMiddleware::class,
                ],
            ],
            'publish' => [
                [
                    'id' => 'AbstractController',
                    'description' => 'replace AbstractController',
                    'source' => __DIR__ . '/../publish/AbstractController.php',
                    'destination' => BASE_PATH . '/app/Controller/AbstractController.php',
                ],
                [
                    'id' => 'ErrorCode',
                    'description' => 'initialization ErrorCode',
                    'source' => __DIR__ . '/../publish/ErrorCode.php',
                    'destination' => BASE_PATH . '/app/Constants/ErrorCode.php',
                ],
                [
                    'id' => 'Model',
                    'description' => 'replace Model',
                    'source' => __DIR__ . '/../publish/Model.php',
                    'destination' => BASE_PATH . '/app/Model/Model.php',
                ],
                [
                    'id' => 'config:exceptions',
                    'description' => 'replace exceptions config',
                    'source' => __DIR__ . '/../publish/config/exceptions.php',
                    'destination' => BASE_PATH . '/config/autoload/exceptions.php',
                ],
                [
                    'id' => 'config:services',
                    'description' => 'replace services config',
                    'source' => __DIR__ . '/../publish/config/services.php',
                    'destination' => BASE_PATH . '/config/autoload/services.php',
                ],
                [
                    'id' => 'config:nacos',
                    'description' => 'replace nacos config',
                    'source' => __DIR__ . '/../publish/config/nacos.php',
                    'destination' => BASE_PATH . '/config/autoload/nacos.php',
                ],
                [
                    'id' => 'config:watcher',
                    'description' => 'replace watcher config',
                    'source' => __DIR__ . '/../publish/config/watcher.php',
                    'destination' => BASE_PATH . '/config/autoload/watcher.php',
                ],
                [
                    'id' => 'config:server',
                    'description' => 'replace server config',
                    'source' => __DIR__ . '/../publish/config/server.php',
                    'destination' => BASE_PATH . '/config/autoload/server.php',
                ],
                [
                    'id' => 'config:config_center',
                    'description' => 'replace config_center config',
                    'source' => __DIR__ . '/../publish/config/config_center.php',
                    'destination' => BASE_PATH . '/config/autoload/config_center.php',
                ],
                [
                    'id' => 'config:aspects',
                    'description' => 'replace aspects config',
                    'source' => __DIR__ . '/../publish/config/aspects.php',
                    'destination' => BASE_PATH . '/config/autoload/aspects.php',
                ],
                [
                    'id' => 'config:middlewares',
                    'description' => 'replace middlewares config',
                    'source' => __DIR__ . '/../publish/config/middlewares.php',
                    'destination' => BASE_PATH . '/config/autoload/middlewares.php',
                ],
                [
                    'id' => 'config:opentracing',
                    'description' => 'replace opentracing config',
                    'source' => __DIR__ . '/../publish/config/opentracing.php',
                    'destination' => BASE_PATH . '/config/autoload/opentracing.php',
                ],
                [
                    'id' => 'config:metric',
                    'description' => 'replace metric config',
                    'source' => __DIR__ . '/../publish/config/metric.php',
                    'destination' => BASE_PATH . '/config/autoload/metric.php',
                ],
                [
                    'id' => 'config:dependencies',
                    'description' => 'replace config dependencies',
                    'source' => __DIR__ . '/../publish/config/dependencies.php',
                    'destination' => BASE_PATH . '/config/autoload/dependencies.php',
                ],
                [
                    'id' => 'config:config',
                    'description' => 'replace config config',
                    'source' => __DIR__ . '/../publish/config/config.php',
                    'destination' => BASE_PATH . '/config/config.php',
                ],
                [
                    'id' => 'config:routes',
                    'description' => 'replace config routes',
                    'source' => __DIR__ . '/../publish/config/routes.php',
                    'destination' => BASE_PATH . '/config/routes.php',
                ],
                [
                    'id' => 'start_hyperf_shell',
                    'description' => 'start hyperf shell',
                    'source' => __DIR__ . '/../publish/start_hyperf.sh',
                    'destination' => BASE_PATH . '/start_hyperf.sh',
                ],
                [
                    'id' => '.env',
                    'description' => 'replace .env',
                    'source' => __DIR__ . '/../publish/.env.example',
                    'destination' => BASE_PATH . '/.env',
                ],
                [
                    'id' => '.env.example',
                    'description' => 'replace .env.example',
                    'source' => __DIR__ . '/../publish/.env.example',
                    'destination' => BASE_PATH . '/.env.example',
                ],
                [
                    'id' => '.php-cs-fixer.php',
                    'description' => 'replace .php-cs-fixer.php',
                    'source' => __DIR__ . '/../publish/.php-cs-fixer.php',
                    'destination' => BASE_PATH . '/.php-cs-fixer.php',
                ],
                [
                    'id' => 'README.md',
                    'description' => 'replace README.md',
                    'source' => __DIR__ . '/../publish/README.md',
                    'destination' => BASE_PATH . '/README.md',
                ],
                [
                    'id' => 'Dockerfile',
                    'description' => 'replace Dockerfile',
                    'source' => __DIR__ . '/../publish/Dockerfile',
                    'destination' => BASE_PATH . '/Dockerfile',
                ],
                [
                    'id' => 'create_model_has_attachments_table',
                    'description' => 'create_model_has_attachments_table',
                    'source' => __DIR__ . '/../publish/1111_01_11_111111_create_model_has_attachments_table.php',
                    'destination' => BASE_PATH . '/migrations/1111_01_11_111111_create_model_has_attachments_table.php',
                ],
            ],
        ];
    }
}
