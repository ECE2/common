<?php

declare(strict_types=1);

namespace Ece2\Common\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * excel 导入导出元数据.
 * @Annotation 
 */
#[Attribute(Attribute::TARGET_CLASS)]
class ExcelData extends AbstractAnnotation
{
}
