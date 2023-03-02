<?php

declare(strict_types=1);

namespace Ece2\Common\Aspect;

use Ece2\Common\Annotation\DeleteCache;
use Hyperf\Config\Annotation\Value;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Di\Exception\Exception;
use Hyperf\Utils\Str;

#[Aspect]
class DeleteCacheAspect extends AbstractAspect
{
    public array $annotations = [
        DeleteCache::class
    ];

    /**
     * 缓存前缀
     */
    #[Value("cache.default.prefix")]
    protected string $prefix;

    /**
     * @param ProceedingJoinPoint $proceedingJoinPoint
     * @return mixed
     * @throws Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        /** @var DeleteCache $deleteCache */
        $deleteCache = $proceedingJoinPoint->getAnnotationMetadata()->method[DeleteCache::class];
        if (!empty($deleteCache->keys)) {
            $redis = redis();
            $keys = explode(',', $deleteCache->keys);
            foreach ($keys as $key) {
                if (!Str::contains($key, '*')) {
                    $redis->del("{$this->prefix}{$key}");
                } else {
                    $keyList = $redis->keys("{$this->prefix}{$key}");
                    if ($redis->exists($keyList)) {
                        $redis->del(...$keyList);
                    }
                }
            }
        }

        return $proceedingJoinPoint->process();
    }
}
