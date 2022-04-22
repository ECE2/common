<?php

declare (strict_types=1);

namespace Ece2\Common\Abstracts;

use Hyperf\Config\Annotation\Value;

abstract class AbstractRedis
{
    /**
     * 缓存前缀
     */
    #[Value("cache.default.prefix")]
    protected string $prefix;

    /**
     * key 类型名
     */
    protected string $typeName;

    /**
     * 获取实例
     * @return mixed
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public static function getInstance()
    {
        return container()->get(static::class);
    }

    /**
     * 获取redis实例
     * @return \Hyperf\Redis\Redis
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function redis(): \Hyperf\Redis\Redis
    {
        return redis();
    }

    /**
     * 获取key
     * @param string $key
     * @return string|null
     */
    public function getKey(string $key): ?string
    {
        return empty($key) ? null : ($this->prefix . trim($this->typeName, ':') . ':' . $key);
    }

}
