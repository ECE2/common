<?php

declare(strict_types=1);
use Hyperf\ConfigCenter\Mode;

return [
    'enable' => (bool) env('CONFIG_CENTER_ENABLE', true),
    'driver' => env('CONFIG_CENTER_DRIVER', 'nacos'),
    'mode' => env('CONFIG_CENTER_MODE', Mode::PROCESS),
    'drivers' => [
        'nacos' => [
            'driver' => Hyperf\ConfigNacos\NacosDriver::class,
            'merge_mode' => Hyperf\ConfigNacos\Constants::CONFIG_MERGE_OVERWRITE,
            'interval' => 3,
            'default_key' => 'config_center.system',
            'listener_config' => [
                // 使用举例: config('config_center.system.super_admin')
//                'config_center.system' => [
//                    'tenant' => 'public', // 命名空间
//                    'data_id' => 'system',
//                    'group' => 'DEFAULT_GROUP',
//                    'type' => 'json',
//                ],
            ],
        ],
    ],
];
