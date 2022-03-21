<?php

declare(strict_types=1);

return [
    'default' => env('TRACER_DRIVER', 'zipkin'),
    'enable' => [
        'guzzle' => env('TRACER_ENABLE_GUZZLE', true),
        'redis' => env('TRACER_ENABLE_REDIS', true),
        'db' => env('TRACER_ENABLE_DB', true),
        'method' => env('TRACER_ENABLE_METHOD', true),
    ],
    'tracer' => [
        'zipkin' => [
            'driver' => \Hyperf\Tracer\Adapter\ZipkinTracerFactory::class,
            'app' => [
                'name' => env('APP_NAME', 'skeleton'),
                // Hyperf will detect the system info automatically as the value if ipv4, ipv6, port is null
                'ipv4' => env('APP_HOST', '0.0.0.0'),
                'ipv6' => null,
                'port' => (int) env('APP_HTTP_PORT', 9501),
            ],
            'options' => [
                'endpoint_url' => env('ZIPKIN_ENDPOINT_URL', 'http://localhost:9411/api/v2/spans'),
                'timeout' => env('ZIPKIN_TIMEOUT', 1),
            ],
            // 开发/测试 环境 100% 收集, 其他环境 1% 收集率
            'sampler' => in_array(env('APP_ENV', 'dev'), ['dev', 'stage'], true) ? \Zipkin\Samplers\BinarySampler::createAsAlwaysSample() : \Zipkin\Samplers\PercentageSampler::create(0.02),
        ],
    ],
];
