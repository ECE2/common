<?php

declare(strict_types=1);

namespace Ece2\Common\Interfaces;

/**
 * key/value 枚举接口
 */
interface KeyValueEnumInterface
{
    public function key();

    public function value();
}
