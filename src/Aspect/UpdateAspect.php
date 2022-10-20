<?php

declare(strict_types=1);

namespace Ece2\Common\Aspect;

use Ece2\Common\Abstracts\AbstractModel;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Di\Exception\Exception;

/**
 * update 时自动设置更新人
 */
#[Aspect]
class UpdateAspect extends AbstractAspect
{
    public array $classes = [
        'Ece2\Common\Abstracts\AbstractModel::update'
    ];

    /**
     * @param ProceedingJoinPoint $proceedingJoinPoint
     * @return mixed
     * @throws Exception
     */
    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $instance = $proceedingJoinPoint->getInstance();
        if ($instance instanceof AbstractModel &&
            method_exists($instance, 'getUpdatedByColumn') &&
            !empty($operatorId = identity()?->getKey())) {
            $instance->{$instance->getUpdatedByColumn()} = $operatorId;
        }

        return $proceedingJoinPoint->process();
    }
}
