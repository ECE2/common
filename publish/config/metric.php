<?php

declare(strict_types=1);

use Hyperf\Metric\Adapter\Prometheus\Constants;

use function Hyperf\Support\env;

return [
    // To disable hyperf/metric temporarily, set default driver to noop.
    'default' => env('METRIC_DRIVER', 'noop'),
    'use_standalone_process' => env('METRIC_USE_STANDALONE_PROCESS', true),
    'enable_default_metric' => env('METRIC_ENABLE_DEFAULT_METRIC', true),
    'default_metric_interval' => env('DEFAULT_METRIC_INTERVAL', 5),
    'metric' => [
        'prometheus' => [
            'driver' => Hyperf\Metric\Adapter\Prometheus\MetricFactory::class,
            'mode' => Constants::PUSH_MODE,
            'namespace' => env('APP_NAME', 'skeleton'),
            'scrape_host' => env('PROMETHEUS_SCRAPE_HOST', '0.0.0.0'),
            'scrape_port' => env('PROMETHEUS_SCRAPE_PORT', '9502'),
            'scrape_path' => env('PROMETHEUS_SCRAPE_PATH', '/metrics'),
            'push_host' => env('PROMETHEUS_PUSH_HOST', '0.0.0.0'),
            'push_port' => env('PROMETHEUS_PUSH_PORT', '9091'),
            'push_interval' => env('PROMETHEUS_PUSH_INTERVAL', 5),
        ],
        'noop' => [
            'driver' => Hyperf\Metric\Adapter\NoOp\MetricFactory::class,
        ],
    ],
];
