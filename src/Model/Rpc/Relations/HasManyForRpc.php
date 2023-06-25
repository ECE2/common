<?php

declare(strict_types=1);

namespace Ece2\Common\Model\Rpc\Relations;

use Ece2\Common\Model\Rpc\Model\Base;
use Hyperf\Collection\Arr;
use Hyperf\Database\Model\Relations\HasMany;

class HasManyForRpc extends HasMany
{
    public function getEager()
    {
        $builder = $this->applyScopes();

        /** @var Base $related */
        $related = $this->getRelated();

        // TODO 这里还没测到, 应该可用
        // 处理 N + 1 问题
        if (count($models = $related::getByWhereRaw($this->getWhereRawSql())) > 0) {
            $models = $builder->eagerLoadRelations($models);
        }

        return $builder->getModel()->newCollection($models);
    }

    /**
     * 重写 relation getAttribute 获取.
     * @return null|Base|\Hyperf\Database\Model\Builder|\Hyperf\Database\Model\Model|object
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function getResults()
    {
        /** @var Base $related */
        $related = $this->getRelated();

        return $related->setRawAttributes($related::getByWhereRaw($this->getWhereRawSql()), true);
    }

    private function getWhereRawSql()
    {
        $values = (array) Arr::get(
            $this->getBaseQuery()->wheres,
            '0.values',
            Arr::get($this->getBaseQuery()->wheres, '0.value', [])
        );

        return sprintf($this->getForeignKeyName() . ' in (%s)', implode(',', $values));
    }
}
