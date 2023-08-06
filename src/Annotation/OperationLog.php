<?php

declare(strict_types=1);

namespace Ece2\Common\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * 记录操作日志注解.
 * @Annotation 
 */
#[Attribute(Attribute::TARGET_METHOD)]
class OperationLog extends AbstractAnnotation
{
    /**
     * @param string $menuName 菜单名称
     */
    public function __construct(
        public string $menuName = ''
    ) {
    }
}
