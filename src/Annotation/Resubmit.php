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
    public int $second = 3;

    /**
     * 提示信息
     * @var string
     */
    public string $message;

    public function __construct($value, $message = null)
    {
        parent::__construct($value);

        $this->bindMainProperty('second', [$value]);
        $this->bindMainProperty('message', [$message]);
    }
}
