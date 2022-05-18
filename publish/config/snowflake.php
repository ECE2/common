<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

use Hyperf\Snowflake\MetaGenerator\RedisMilliSecondMetaGenerator;
use Hyperf\Snowflake\MetaGenerator\RedisSecondMetaGenerator;

return [
    'begin_second' => 1631439480, // 雪花计算开始时间 (注意: 所有微服务项目得一致, 且一旦项目生产运行后, 不能修改此项)
    RedisMilliSecondMetaGenerator::class => [
        'pool' => 'default',
    ],
    RedisSecondMetaGenerator::class => [
        'pool' => 'default',
    ],
];
