<?php

declare(strict_types=1);

use function Hyperf\Support\env;

return [
    'host' => env('NACOS_HOST', '127.0.0.1'),
    'port' => env('NACOS_PORT', 8848),
    'username' => env('NACOS_USER', 'nacos'),
    'password' => env('NACOS_PASSWORD', 'nacos'),
    'guzzle' => [
        'config' => null,
    ],
];
