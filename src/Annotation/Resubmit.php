<?php

declare(strict_types=1);

namespace Ece2\Common\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * 禁止重复提交
 * @Annotation 
 */
#[Attribute(Attribute::TARGET_METHOD)]
class Resubmit extends AbstractAnnotation
{
    /**
     * @param int $second
     * @param string|null $message 提示信息
     */
    public function __construct(
        public int $second = 3,
        public ?string $message = null
    ) {
    }
}
