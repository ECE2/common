<?php

declare(strict_types=1);

use Hyperf\Contract\StdoutLoggerInterface;
use Psr\Log\LogLevel;

return [
    'app_name' => env('APP_NAME', 'skeleton'),
    'app_env' => env('APP_ENV', 'dev'),
    'scan_cacheable' => env('SCAN_CACHEABLE', false),
    StdoutLoggerInterface::class => [
        'log_level' => env('APP_ENV', 'dev') === 'dev' ? [
            LogLevel::EMERGENCY,
            LogLevel::ALERT,
            LogLevel::CRITICAL,
            LogLevel::ERROR,
            LogLevel::WARNING,
            LogLevel::NOTICE,
            LogLevel::INFO,
            LogLevel::DEBUG,
        ] : [
            LogLevel::EMERGENCY,
            LogLevel::ALERT,
            LogLevel::CRITICAL,
            LogLevel::ERROR,
            LogLevel::WARNING,
            LogLevel::NOTICE,
            LogLevel::INFO,
        ],
    ],
];
