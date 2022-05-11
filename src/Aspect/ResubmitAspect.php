<?php

declare(strict_types=1);

namespace Ece2\Common\Aspect;

use Ece2\Common\Annotation\Resubmit;
use Ece2\Common\Exception\NormalStatusException;
use Hyperf\HttpServer\Request;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Di\Exception\Exception;
use Lysice\HyperfRedisLock\LockTimeoutException;

#[Aspect]
class ResubmitAspect extends AbstractAspect
{
    public $annotations = [
        Resubmit::class
    ];

    /**
     * @param ProceedingJoinPoint $proceedingJoinPoint
     * @return mixed
     * @throws Exception
     * @throws \Throwable
     */
    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        /** @var $resubmit Resubmit */
        if (isset($proceedingJoinPoint->getAnnotationMetadata()->method[Resubmit::class])) {
            $resubmit = $proceedingJoinPoint->getAnnotationMetadata()->method[Resubmit::class];
        }

        $request = container()->get(Request::class);
        $key = md5(sprintf('%s-%s-%s', $request->ip(), $request->getPathInfo(), $request->getMethod()));

        $lock = new \Lysice\HyperfRedisLock\RedisLock(redis(), 'resubmit:' . $key, 60);
        try {
            $lock->block($resubmit->second, fn() => true);
        } catch (LockTimeoutException $e) {
            throw new NormalStatusException($resubmit->message ?: t('resubmit'), 500);
        }

        return $proceedingJoinPoint->process();
    }
}
