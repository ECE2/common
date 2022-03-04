<?php

declare(strict_types=1);

// 文档: https://hyperf.wiki/2.2/#/zh-cn/json-rpc
$consumersRegistry = [
    'protocol' => 'nacos',
    'address' => sprintf('http://%s:%s', env('NACOS_HOST'), env('NACOS_PORT')),
];

return [
    'enable' => [
        'discovery' => true,
        'register' => true,
    ],
    'consumers' => value(function () use ($consumersRegistry) {
        $consumers = [];
        // 这里示例自动创建代理消费者类的配置形式，顾存在 name 和 service 两个配置项，这里的做法不是唯一的，仅说明可以通过 PHP 代码来生成配置
        // 下面的 FooServiceInterface 和 BarServiceInterface 仅示例多服务，并不是在文档示例中真实存在的
        $services = [
        ];
        foreach ($services as $name => $interface) {
            $consumers[] = [
                'name' => $name,
                'service' => $interface,
                'registry' => $consumersRegistry,
            ];
        }
        return $consumers;
    }),
    'providers' => [],
    'drivers' => [
        'nacos' => [
            // nacos server url like https://nacos.hyperf.io, Priority is higher than host:port
            // 'url' => '',
            // The nacos host info
            'host' => env('NACOS_HOST', '127.0.0.1'),
            'port' => env('NACOS_PORT', 8848),
            // The nacos account info
            'username' => env('NACOS_USER', 'nacos'),
            'password' => env('NACOS_PASSWORD', 'nacos'),
            'guzzle' => [
                'config' => null,
            ],
            'group_name' => env('NACOS_GROUP_NAME', 'DEFAULT_GROUP'),
            'namespace_id' => env('NACOS_NAMESPACE_ID', 'public'),
            'heartbeat' => 5,
        ],
    ],
];
