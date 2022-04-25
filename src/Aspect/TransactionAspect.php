<?php

declare(strict_types=1);

namespace Ece2\Common\Aspect;

use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Ece2\Common\Annotation\Transaction;

#[Aspect]
class TransactionAspect extends AbstractAspect
{
    public $annotations = [
        Transaction::class
    ];

    /**
     * @param ProceedingJoinPoint $proceedingJoinPoint
     * @return mixed
     */
    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if (isset($proceedingJoinPoint->getAnnotationMetadata()->method[Transaction::class])) {
            /** @var Transaction $transaction */
            $transaction = $proceedingJoinPoint->getAnnotationMetadata()->method[Transaction::class];
        }

        return Db::transaction(
            static fn () => $proceedingJoinPoint->process(),
            $transaction->retry
        );
    }
}
