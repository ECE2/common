<?php

declare(strict_types=1);

namespace Ece2\Common\Aspect;

use Ece2\Common\Abstracts\AbstractModel;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Di\Exception\Exception;

/**
 * save 时, 自动设置 创建人 更新人 (TODO: company_id) 和 id
 */
#[Aspect]
class SaveAspect extends AbstractAspect
{
    public $classes = [
        'Ece2\Common\Abstracts\AbstractModel::save'
    ];

    /**
     * @param ProceedingJoinPoint $proceedingJoinPoint
     * @return mixed
     * @throws Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $instance = $proceedingJoinPoint->getInstance();

        if (!empty($operatorId = identity()?->getKey())) {
            // 设置创建人
            if ($instance instanceof AbstractModel &&
                method_exists($instance, 'getCreatedByColumn') &&
                empty($instance->{$instance->getCreatedByColumn()})
            ) {
                $instance->{$instance->getCreatedByColumn()} = $operatorId;
            }

            // 设置更新人
            if ($instance instanceof AbstractModel && method_exists($instance, 'getUpdatedByColumn')) {
                $instance->{$instance->getUpdatedByColumn()} = $operatorId;
            }
        }

//        // 生成ID
//        if ($instance instanceof AbstractModel &&
//            !$instance->getIncrementing() &&
//            $instance->getPrimaryKeyType() === 'int' &&
//            empty($instance->{$instance->getKeyName()})
//        ) {
//            $instance->setPrimaryKeyValue(snowflake_id());
//        }

        return $proceedingJoinPoint->process();
    }
}
