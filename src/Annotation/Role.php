<?php

declare(strict_types=1);

namespace Ece2\Common\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * 用户角色验证.
 * @Annotation
 */
#[Attribute(Attribute::TARGET_METHOD)]
class Role extends AbstractAnnotation
{
    /**
     * @param string|null $code 角色代码标识
     * @param string $where 多个角色代码, 过滤条件 为 OR 时, 检查有一个通过则全部通过 为 AND 时, 检查有一个不通过则全不通过
     */
    public function __construct(
        public ?string $code = null,
        public string $where = 'OR'
    ) {
    }
}
