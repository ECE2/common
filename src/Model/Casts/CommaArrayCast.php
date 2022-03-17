<?php

declare(strict_types=1);
/**
 * This file is part of api template.
 */
namespace Ece2\Common\Model\Casts;

use Hyperf\Contract\CastsAttributes;
use Hyperf\Utils\Contracts\Arrayable;
use Hyperf\Utils\Stringable;

/**
 * Model Casts
 * 逗号分隔
 * 举例: 数据库存储的是 'a,b,c' (取出)使用时自动转为 ['a', 'b', 'c']
 * 保存时反之.
 */
class CommaArrayCast implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes)
    {
        if (is_string($value) || ($value instanceof Stringable && $value = $value->value)) {
            if ($value === '') {
                return [];
            }

            return explode(',', $value);
        }

        return $value;
    }

    public function set($model, string $key, $value, array $attributes)
    {
        if (is_array($value) || ($value instanceof Arrayable && $value = $value->toArray())) {
            return implode(',', (array) $value);
        }

        return $value;
    }
}
