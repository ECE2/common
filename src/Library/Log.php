<?php

declare(strict_types=1);

namespace Ece2\Common\Library;

use Hyperf\Logger\LoggerFactory;
use Hyperf\Utils\ApplicationContext;
use Psr\Log\LoggerInterface;

/**
 * @method static emergency(string|\Stringable $message, array $context = [], string $logName = 'log')
 * @method static alert(string|\Stringable $message, array $context = [], string $logName = 'log')
 * @method static critical(string|\Stringable $message, array $context = [], string $logName = 'log')
 * @method static error(string|\Stringable $message, array $context = [], string $logName = 'log')
 * @method static warning(string|\Stringable $message, array $context = [], string $logName = 'log')
 * @method static notice(string|\Stringable $message, array $context = [], string $logName = 'log')
 * @method static info(string|\Stringable $message, array $context = [], string $logName = 'log')
 * @method static debug(string|\Stringable $message, array $context = [], string $logName = 'log')
 * @method static log($level, string|\Stringable $message, array $context = [], string $logName = 'log')
 */
class Log
{
    public static function __callStatic($logLevel, $arguments)
    {
        if ($logLevel === 'log') {
            @[$logLevel, $message, $context, $logName] = $arguments;
        } else {
            @[$message, $context, $logName] = $arguments;
        }

        static::get($logName ?? 'log')->{$logLevel}($message ?? '', $context ?? []);
    }

    public static function get(string $name = 'log'): LoggerInterface
    {
        return ApplicationContext::getContainer()->get(LoggerFactory::class)->get($name);
    }
}
