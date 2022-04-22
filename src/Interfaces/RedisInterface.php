<?php

namespace Ece2\Common\Interfaces;

interface RedisInterface
{
    /**
     * 设置 key 类型名
     * @param string $typeName
     */
    public function setTypeName(string $typeName): void;

    /**
     * 获取key 类型名
     * @return string
     */
    public function getTypeName(): string;
}
