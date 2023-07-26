<?php

declare(strict_types=1);

namespace Ece2\Common;

use Ece2\Common\Interfaces\JwtTokenAuthInfoInterface;
use Ece2\Common\Library\NamespaceCI;
use Ece2\Common\Middleware\CompanyMiddleware;
use Ece2\Common\Middleware\CorsMiddleware;
use Hyperf\Collection\Arr;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Di\ReflectionManager;
use Hyperf\Support\IPReader;

use Symfony\Component\Finder\Finder;
use function Hyperf\Support\env;
use function Hyperf\Support\value;

class ConfigProvider
{
    public function __invoke(): array
    {
        // 文档: https://hyperf.wiki/2.2/#/zh-cn/json-rpc
        $consumersRegistry = [
            'protocol' => 'nacos',
            'address' => sprintf('http://%s:%s', env('NACOS_HOST'), env('NACOS_PORT')),
        ];

        Blueprint::macro('operators', function () {
            /** @var Blueprint $this */
            $this->unsignedBigInteger('created_by')->default(0)->comment('创建者');
            $this->unsignedBigInteger('updated_by')->default(0)->comment('更新者');
            $this->index('created_by');
        });

        Blueprint::macro('company', function () {
            /** @var Blueprint $this */
            $this->unsignedBigInteger('company_id')->default(0)->comment('公司 ID');
            $this->index('company_id');
        });

        // json rpc contract 反射数据
        $jsonRpcContractReflectionClass = ReflectionManager::getAllClasses([__DIR__ . '/JsonRpc/Contract']);
        // rpc model 反射数据
        $rpcModelReflectionClass = ReflectionManager::getAllClasses([__DIR__ . '/Model/Rpc/Model']);
        // system model 反射数据
        $systemModelReflectionClass = ReflectionManager::getAllClasses([BASE_PATH . '/app/Model']);

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
                        // 使用 RedisSecondMetaGenerator 替换 MetaGeneratorFactory 内返回, 主要使用 秒级 来达到减小雪花 ID 长度
                        \Hyperf\Snowflake\MetaGeneratorFactory::class => __DIR__ . '/../class_map/MetaGeneratorFactory.php',
                        // 替换 Filesystem 增加自定义函数, 主要 getUrl
                        \League\Flysystem\Filesystem::class => __DIR__ . '/../class_map/Filesystem.php',
                        // 替换 (ali oss) Adapter 增加自定义函数, 主要 getUrl
                        \Hyperf\Flysystem\OSS\Adapter::class => __DIR__ . '/../class_map/Adapter.php',
                    ],
                    'ignore_annotations' => [
                        'required',
                    ],
                ],
            ],
            'auth' => [
                // 会是以下这样: 'api' 是 auth.guards key, 也就是 Auth('api'), 当然默认值是 api, 也可以是 Auth('member')
                //  [
                //      'api' => [
                //          'rpc_interface' => 'Ece2\Common\JsonRpc\Contract\SystemUserServiceInterface',
                //          'rpc_model' => 'Ece2\Common\Model\Rpc\Model\SystemUser',
                //          'system_model' => 'App\Model\SystemUser',
                //      ],
                //      'member' => [
                //          'rpc_interface' => 'Ece2\Common\JsonRpc\Contract\SystemMemberServiceInterface',
                //          'rpc_model' => 'Ece2\Common\Model\Rpc\Model\SystemMember',
                //          'system_model' => 'App\Model\SystemMember',
                //      ]
                //  ]
                'guards_provider' => Arr::mapWithKeys(
                    // 有 getInfoByJwtToken method 的 interface, (原本想用接口继承规范 getInfoByJwtToken 方法, 但是生成的代理类有问题, 会生成重复的 getInfoByJwtToken, 把父类的 getInfoByJwtToken 和子类的放在了一块)
                    Arr::where($jsonRpcContractReflectionClass, fn (\ReflectionClass $reflectionClass) => $reflectionClass->hasMethod('getInfoByJwtToken')),
                    function (\ReflectionClass $jsonRpcContractInterface) use ($rpcModelReflectionClass, $systemModelReflectionClass) {
                        // 获取 getInfoByJwtToken 方法 scene 的默认值, 作为 guards key
                        /** @var \ReflectionParameter $parameter */
                        $parameter = Arr::first(
                            $jsonRpcContractInterface->getMethod('getInfoByJwtToken')->getParameters(),
                            fn(\ReflectionParameter $parameter) => $parameter->getName() === 'scene'
                        );

                        /** @var \ReflectionClass $rpcModel */
                        $rpcModel = Arr::first(
                            $rpcModelReflectionClass,
                            // 从 Model/Rpc/Model 下, 找对应的 rpc model, 规则比如: 'rpc_interface' => SystemUserServiceInterface::class, 这里找的 rpc model 就是 SystemUser::class
                            fn (\ReflectionClass $rpcModelClass) => $rpcModelClass->getShortName() === substr($jsonRpcContractInterface->getShortName(), 0, -16)
                        );

                        /** @var \ReflectionClass $systemModel */
                        $systemModel = Arr::first(
                            $systemModelReflectionClass,
                            // 从 app/Model 下, 找对应的 system model, 规则比如: 'rpc_interface' => SystemUserServiceInterface::class, 这里找的 system model 就是 SystemUser::class
                            fn (\ReflectionClass $systemModelClass) => $systemModelClass->getShortName() === substr($jsonRpcContractInterface->getShortName(), 0, -16)
                        );

                        return [
                            $parameter->getDefaultValue() => [ // guard key
                                'rpc_interface' => $jsonRpcContractInterface->getName(), // rpc service interface
                                'rpc_model' => $rpcModel->getName(), // rpc 包装的 model
                                'system_model' => $systemModel?->getName() ?? '', // 当 system 接收到 rpc 请求时包装的 model, 其他系统中 无用
                            ]
                        ];
                    }
                ),
            ],
            'services' => [
                'consumers' => value(function () use ($consumersRegistry, $jsonRpcContractReflectionClass) {
                    $consumers = [];
                    // 这里示例自动创建代理消费者类的配置形式，顾存在 name 和 service 两个配置项，这里的做法不是唯一的，仅说明可以通过 PHP 代码来生成配置
                    // 下面的 FooServiceInterface 和 BarServiceInterface 仅示例多服务，并不是在文档示例中真实存在的
                    /** @var \ReflectionClass $class */
                    foreach ($jsonRpcContractReflectionClass as $class) {
                        $consumers[] = [
                            'name' => substr($class->getShortName(), 0, -9),
                            'service' => $class->getName(),
                            'registry' => $consumersRegistry,
                        ];
                    }

                    return $consumers;
                }),
            ],
            'middlewares' => [
                'http' => [
                    CorsMiddleware::class,
                    CompanyMiddleware::class,
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
                    'id' => 'AbstractService',
                    'description' => 'replace AbstractService',
                    'source' => __DIR__ . '/../publish/AbstractService.php',
                    'destination' => BASE_PATH . '/app/Service/AbstractService.php',
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
                    'id' => 'config:redis',
                    'description' => 'replace config redis',
                    'source' => __DIR__ . '/../publish/config/redis.php',
                    'destination' => BASE_PATH . '/config/autoload/redis.php',
                ],
                [
                    'id' => 'config:snowflake',
                    'description' => 'replace config snowflake',
                    'source' => __DIR__ . '/../publish/config/snowflake.php',
                    'destination' => BASE_PATH . '/config/autoload/snowflake.php',
                ],
                [
                    'id' => 'config:amqp',
                    'description' => 'replace config amqp',
                    'source' => __DIR__ . '/../publish/config/amqp.php',
                    'destination' => BASE_PATH . '/config/autoload/amqp.php',
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
                    'id' => 'DbQueryExecutedListener',
                    'description' => 'Annotation DbQueryExecutedListener',
                    'source' => __DIR__ . '/../publish/DbQueryExecutedListener.php',
                    'destination' => BASE_PATH . '/app/Listener/DbQueryExecutedListener.php',
                ],
                [
                    'id' => '_ide_helper.php',
                    'description' => 'publish _ide_helper.php',
                    'source' => __DIR__ . '/../publish/_ide_helper.php',
                    'destination' => BASE_PATH . '/_ide_helper.php',
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
