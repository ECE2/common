<?php

declare (strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Ece2\Common\Listener;

use Ece2\Common\Library\Log;
use Hyperf\Collection\Arr;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Database\Events\QueryExecuted;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;

#[Listener]
class DbQueryExecutedListener implements ListenerInterface
{
    public function __construct(protected StdoutLoggerInterface $console)
    {
    }

    public function listen(): array
    {
        return [QueryExecuted::class];
    }

    /**
     * @param QueryExecuted $event
     */
    public function process(object $event): void
    {
        if (!($event instanceof QueryExecuted) || config('app_env') !== 'dev') {
            return;
        }

        $sql = $event->sql;
        if (! Arr::isAssoc($event->bindings)) {
            $position = 0;
            foreach ($event->bindings as $value) {
                $position = strpos($sql, '?', $position);
                if ($position === false) {
                    break;
                }
                $value = "'{$value}'";
                $sql = substr_replace($sql, $value, $position, 1);
                $position += strlen($value);
            }
        }

        Log::info(sprintf('[%s] %s', $event->time, $sql), [], 'sql');
    }
}
