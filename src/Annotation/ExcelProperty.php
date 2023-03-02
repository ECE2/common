<?php

declare(strict_types=1);

namespace Ece2\Common\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * excel导入导出元数据。
 * @Annotation 
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class ExcelProperty extends AbstractAnnotation
{
    /**
     * 列表头名称
     * @var string
     */
    public string $value;

    /**
     * 列顺序
     * @var int
     */
    public int $index;

    /**
     * 宽度
     * @var int
     */
    public int $width;

    /**
     * 对齐方式，默认居左
     * @var string
     */
    public string $align;

    /**
     * 列表头字体颜色
     * @var string
     */
    public string $headColor;

    /**
     * 列表头背景颜色
     * @var string
     */
    public string $headBgColor;

    /**
     * 列表体字体颜色
     * @var string
     */
    public string $color;

    /**
     * 列表体背景颜色
     * @var string
     */
    public string $bgColor;
}
