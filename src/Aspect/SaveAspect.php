<?php

declare(strict_types=1);

namespace Ece2\Common\Aspect;

use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Di\Exception\Exception;
use Ece2\Common\Model\Model;

/**
 * save 时, 自动设置 创建人 更新人 (TODO: company_id) 和 id
 */
#[Aspect]
class SaveAspect extends AbstractAspect
{
    public $classes = [
        'Ece2\Common\Model\Model::save'
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

        try {
            // 设置创建人
            if ($instance instanceof Model &&
                in_array('created_by', $instance->getFillable()) &&
                is_null($instance->created_by)
            ) {
                $instance->created_by = identity()?->getKey();
            }

            // 设置更新人
            if ($instance instanceof Model && in_array('updated_by', $instance->getFillable())) {
                $instance->updated_by = identity()?->getKey();
            }
        } catch (\Throwable $e) {
        }

        // 生成ID
        if ($instance instanceof Model &&
            !$instance->incrementing &&
            $instance->getPrimaryKeyType() === 'int' &&
            empty($instance->{$instance->getKeyName()})
        ) {
            $instance->setPrimaryKeyValue(snowflake_id());
        }
        return $proceedingJoinPoint->process();
    }
}
