<?php

declare(strict_types=1);

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
