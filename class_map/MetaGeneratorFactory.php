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
namespace Hyperf\Snowflake;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Snowflake\MetaGenerator\RedisMilliSecondMetaGenerator;
use Hyperf\Snowflake\MetaGenerator\RedisSecondMetaGenerator;
use Psr\Container\ContainerInterface;

class MetaGeneratorFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get(ConfigInterface::class);
        $beginSecond = $config->get('snowflake.begin_second', MetaGeneratorInterface::DEFAULT_BEGIN_SECOND);

        return make(RedisSecondMetaGenerator::class, [
            $container->get(ConfigurationInterface::class),
            $beginSecond,
            $config,
        ]);
    }
}
