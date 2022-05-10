<?php

declare(strict_types=1);

namespace Ece2\Common\Model\Traits;

trait Operator
{
    public function touch(): bool
    {
        $this->updateOperator();

        return $this->save();
    }

    public function updateOperator()
    {
        $operator = identity()['id'];

        if (! $this->isDirty($this->getUpdatedByColumn())) {
            $this->setUpdatedBy($operator);
        }

        if (! $this->exists && ! $this->isDirty($this->getCreatedByColumn())) {
            $this->setCreatedBy($operator);
        }
    }

    public function setUpdatedBy($value)
    {
        $this->{$this->getUpdatedByColumn()} = $value;

        return $this;
    }

    public function setCreatedBy($value)
    {
        $this->{$this->getCreatedByColumn()} = $value;

        return $this;
    }

    /**
     * 获取新建操作人字段
     *
     * @return string
     */
    public function getCreatedByColumn()
    {
        return defined('static::CREATED_BY') ? static::CREATED_BY : 'created_by';
    }

    /**
     * 获取更新操作人字段
     *
     * @return string
     */
    public function getUpdatedByColumn()
    {
        return defined('static::UPDATED_BY') ? static::UPDATED_BY : 'updated_by';
    }

    public function getQualifiedCreatedByColumn()
    {
        return $this->qualifyColumn($this->getCreatedByColumn());
    }

    public function getQualifiedUpdatedByColumn()
    {
        return $this->qualifyColumn($this->getUpdatedByColumn());
    }
}
