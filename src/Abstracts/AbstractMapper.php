<?php

declare (strict_types = 1);

namespace Ece2\Common\Abstracts;

use Hyperf\Context\Context;
use Ece2\Common\Model\Model;
use Ece2\Common\Traits\MapperTrait;

abstract class AbstractMapper
{
    use MapperTrait;

    /**
     * @var Model
     */
    public $model;

    abstract public function assignModel();

    public function __construct()
    {
        $this->assignModel();
    }

    /**
     * 把数据设置为类属性
     * @param array $data
     */
    public static function setAttributes(array $data)
    {
        Context::set('attributes', $data);
    }

    /**
     * 魔术方法，从类属性里获取数据
     * @param string $name
     * @return mixed|string
     */
    public function __get(string $name)
    {
        return $this->getAttributes()[$name] ?? '';
    }

    /**
     * 获取数据
     * @return array
     */
    public function getAttributes(): array
    {
        return Context::get('attributes', []);
    }
}
