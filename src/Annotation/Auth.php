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
    /**
     * @param string $scene 验证场景
     * @param bool $mustLogin 必须登录,不然抛错
     */
    public function __construct(
        public string $scene = 'api',
        public bool $mustLogin = true
    ) {
    }
}
