<?php

declare(strict_types=1);

namespace Ece2\Common\Model\Traits;

/**
 * 数据表扩展表单相关操作
 */
trait FormElement
{

    /**
     * 为数据表生成扩展表单关联数据
     * @param string $key 唯一键值，一般为数据的唯一ID
     * @return string
     */
    public static function generateFormElementRelationKey($key): string
    {

        return config('app_name') . '.' . str_replace('\\', '.', self::class) . '.' . $key;
    }
}
