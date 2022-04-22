<?php

declare(strict_types=1);

namespace Ece2\Common\Aspect;

use Ece2\Common\Library\RedisLock;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Di\Exception\Exception;
use Ece2\Common\Annotation\Resubmit;
use Ece2\Common\Exception\NormalStatusException;
use Ece2\Common\Request;

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

        $lockRedis = new RedisLock();
        $lockRedis->setTypeName('resubmit');

        if ($lockRedis->check($key)) {
            $lockRedis = null;
            throw new NormalStatusException($resubmit->message ?: t('resubmit'), 500);
        }

        $lockRedis->lock($key, $resubmit->second);
        $lockRedis = null;

        return $proceedingJoinPoint->process();
    }
}
