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
    public $classes = [
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
        // 更新更改人
        if ($instance instanceof AbstractModel && in_array('updated_by', $instance->getFillable())) {
            $instance->updated_by = identity()?->getKey();
        }

        return $proceedingJoinPoint->process();
    }
}
