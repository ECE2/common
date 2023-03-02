<?php

declare(strict_types=1);

namespace Ece2\Common\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * 删除缓存.
 * @Annotation 
 */
#[Attribute(Attribute::TARGET_METHOD)]
class DeleteCache extends AbstractAnnotation
{
    /**
     * 缓存 key, 多个以逗号分开
     * @var string
     */
    public string $keys;

    public function __construct($value = null)
    {
        parent::__construct($value);

        $this->bindMainProperty('keys', [$value]);
    }
}
