<?php

declare(strict_types=1);

namespace Ece2\Common\Aspect;

use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Di\Exception\Exception;

/**
 * 自动设置 创建人 更新人 (TODO: company_id) 和 id
 */
#[Aspect]
class OperatorAspect extends AbstractAspect
{
    public $classes = [
        'Hyperf\Database\Model\Model::save',
        'Hyperf\Database\Model\Builder::update',
        'Hyperf\Database\Model\Builder::increment',
        'Hyperf\Database\Model\Builder::decrement',
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
        $reflectMethod = $proceedingJoinPoint->getReflectMethod();
        $reflectMethodToString = $reflectMethod->class . '::' . $reflectMethod->name;
        $arguments = $proceedingJoinPoint->arguments;
        if (in_array(
            $reflectMethodToString,
            [
                'Hyperf\Database\Model\Builder::increment',
                'Hyperf\Database\Model\Builder::decrement',
                'Hyperf\Database\Model\Builder::update',
            ], true)) {

            $uid = identity()?->getKey();
            if (empty($uid)) {
                return $proceedingJoinPoint->process();
            }

            $model = $instance->getModel();
            $paramName = $reflectMethodToString === 'Hyperf\Database\Model\Builder::update' ? 'values' : 'extra'; // 被 aop 的函数参数名
            if (method_exists($model, 'updateOperators')) {
                $arguments['keys'][$paramName] = array_merge(
                    [
                        $model->getUpdatedByColumn() => $uid,
                    ],
                    $arguments['keys'][$paramName]
                );
                // 覆盖提交的参数, 塞入更新人
                $proceedingJoinPoint->arguments = $arguments;
            }

        // Hyperf\Database\Model\Model::save 或者说 Hyperf\Database\Model\Model 类时使用了 trait Operator
        } elseif ($instance !== null && method_exists($instance, 'updateOperators')) {
            $instance->updateOperators(); // 更新操作人
        }

        return $proceedingJoinPoint->process();
    }
}
