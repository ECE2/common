<?php

declare(strict_types = 1);

namespace Ece2\Common\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * 用户登录验证.
 * @Annotation
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Auth extends AbstractAnnotation
{
    public string $scene;

    public function __construct($value = 'api')
    {
        parent::__construct($value);

        $this->bindMainProperty('scene', [ $value ]);
    }
}
