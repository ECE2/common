<?php

declare(strict_types=1);

use Ece2\Common\Office\Excel\PhpOffice;
use Ece2\Common\Office\Excel\XlsWriter;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ServiceGovernance\IPReaderInterface;
use Hyperf\Snowflake\IdGeneratorInterface;
use Hyperf\Utils\ApplicationContext;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

use function Hyperf\Support\make;

if (! function_exists('ip')) {
    /**
     * 获取 ip
     */
    function ip($request): string
    {
        $ip = $request->server('remote_addr', '0.0.0.0');
        $headers = $request->getHeaders();

        if (isset($headers['x-real-ip'])) {
            $ip = $headers['x-real-ip'][0];
        } else if (isset($headers['x-forwarded-for'])) {
            $ip = $headers['x-forwarded-for'][0];
        } else if (isset($headers['http_x_forwarded_for'])) {
            $ip = $headers['http_x_forwarded_for'][0];
        }

        return $ip;
    }
}

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

if (! function_exists('collection_import')) {
    /**
     * 数据导入
     * @param string $dto
     * @param $model
     * @param \Closure|null $closure
     * @return bool
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    function collection_import(string $dto, $model, ?\Closure $closure = null): bool
    {
        $excelDrive = config('excel_drive');
        if ($excelDrive === 'auto') {
            $excel = extension_loaded('xlswriter') ? new XlsWriter($dto) : new PhpOffice($dto);
        } else {
            $excel = $excelDrive === 'xlsWriter' ? new XlsWriter($dto) : new PhpOffice($dto);
        }

        return $excel->import($model, $closure);
    }
}

if (! function_exists('collection_export')) {
    /**
     * 导出数据
     * @param string $dto
     * @param string $filename
     * @param array|\Closure|null $closure
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    function collection_export(array $arr, string $dto, string $filename, array|\Closure $closure = null): \Psr\Http\Message\ResponseInterface
    {
        $excelDrive = config('excel_drive');
        if ($excelDrive === 'auto') {
            $excel = extension_loaded('xlswriter') ? new XlsWriter($dto) : new PhpOffice($dto);
        } else {
            $excel = $excelDrive === 'xlsWriter' ? new XlsWriter($dto) : new PhpOffice($dto);
        }

        return $excel->collection_export($filename, is_null($closure) ? $arr : $closure);
    }
}

if (! function_exists('array_to_tree')) {
    /**
     * to 树状
     * @param int|string $parentId
     * @param string $idField
     * @param string $parentField
     * @param string $childrenField
     * @return array
     */
    function array_to_tree(array $data, string $idField = 'id', string $parentField = 'parent_id', string $childrenField = 'children', int|string $parentId = 0): array
    {
        if (empty($data)) {
            return [];
        }
        // id 作为主键
        $data = array_column($data, null, $idField);

        foreach ($data as &$item) {
            $itemParentId = (int) $item[$parentField];
            if ($itemParentId === $parentId) { // 顶级目录不操作
                continue;
            }

            // 指定到父级下
            $data[$itemParentId][$childrenField][] = &$item;
        }
        unset($item);

        // 过滤不是顶级目录
        return array_values(array_filter($data, static fn ($split) => (int) $split[$parentField] === $parentId));
    }
}
