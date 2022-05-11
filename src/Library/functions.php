<?php

declare(strict_types=1);

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ServiceGovernance\IPReaderInterface;
use Hyperf\Snowflake\IdGeneratorInterface;
use Hyperf\Utils\ApplicationContext;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

if (! function_exists('container')) {
    /**
     * 获取容器实例.
     * @return \Psr\Container\ContainerInterface
     */
    function container(): Psr\Container\ContainerInterface
    {
        return ApplicationContext::getContainer();
    }
}

if (! function_exists('identity')) {
    /**
     * 获取上下文内的当前身份信息.
     */
    function identity(): App\Model\SystemUser|null|Ece2\Common\Model\Rpc\Model\SystemUser
    {
        if ($userResolver = context_get('identity')) {
            if (is_callable($userResolver)) {
                return $userResolver();
            }

            return $userResolver;
        }

        return null;
    }
}

if (! function_exists('identity_set')) {
    /**
     * 设置上下文内的当前身份信息.
     * @param $value
     * @return mixed
     */
    function identity_set($value)
    {
        return context_set('identity', $value);
    }
}

if (! function_exists('is_base_system')) {
    /**
     * 当前系统是否为基座.
     * @param $system
     * @return bool
     */
    function is_base_system($system = 'system')
    {
        return config('app_name') === $system;
    }
}

if (! function_exists('redis')) {
    /**
     * 获取Redis实例.
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @return \Hyperf\Redis\Redis
     */
    function redis($poolName = 'default'): Hyperf\Redis\Redis
    {
        return container()->get(\Hyperf\Redis\RedisFactory::class)->get($poolName);
    }
}

if (! function_exists('host')) {
    /**
     * 获取本机 IP 地址
     * @return string
     */
    function host()
    {
        try {
            $host = container()->get(IPReaderInterface::class)->read();
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            $host = '';
        }

        return $host;
    }
}

if (! function_exists('console')) {
    /**
     * 获取控制台输出实例.
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    function console(): StdoutLoggerInterface
    {
        return container()->get(StdoutLoggerInterface::class);
    }
}

if (! function_exists('format_size')) {
    /**
     * 格式化大小.
     */
    function format_size(int $size): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $index = 0;
        for ($i = 0; $size >= 1024 && $i < 5; ++$i) {
            $size /= 1024;
            $index = $i;
        }
        return round($size, 2) . $units[$index];
    }
}

if (! function_exists('t')) {
    /**
     * 多语言函数.
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    function t(string $key, array $replace = []): string
    {
        $acceptLanguage = container()->get(\Hyperf\HttpServer\Request::class)->getHeaderLine('accept-language');
        $language = ! empty($acceptLanguage) ? explode(',', $acceptLanguage)[0] : 'zh_CN';
        return __($key, $replace, $language);
    }
}

if (! function_exists('context_set')) {
    /**
     * 设置上下文数据.
     * @param $data
     */
    function context_set(string $key, $data): bool
    {
        return (bool) \Hyperf\Context\Context::set($key, $data);
    }
}

if (! function_exists('context_get')) {
    /**
     * 获取上下文数据.
     * @return mixed
     */
    function context_get(string $key)
    {
        return \Hyperf\Context\Context::get($key);
    }
}

if (! function_exists('snowflake_id')) {
    /**
     * 生成雪花ID.
     * @param null|mixed $meta
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    function snowflake_id($meta = null)
    {
        return container()->get(IdGeneratorInterface::class)->generate($meta);
    }
}

if (! function_exists('event')) {
    /**
     * 事件调度快捷方法.
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    function event(object $dispatch): object
    {
        return container()->get(EventDispatcherInterface::class)->dispatch($dispatch);
    }
}

if (! function_exists('ip_to_region')) {
    /**
     * 获取 IP 的区域地址
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    function ip_to_region(string $ip): string
    {
        $ip2Region = make(\Ip2Region::class);
        if (empty($ip2Region->btreeSearch($ip)['region'])) {
            return t('jwt.unknown');
        }

        $region = $ip2Region->btreeSearch($ip)['region'];
        [$country, $number, $province, $city, $network] = explode('|', $region);
        if ($country === '中国') {
            return $province . '-' . $city . ':' . $network;
        }
        if ($country === '0') {
            return t('jwt.unknown');
        }
        return $country;
    }
}
