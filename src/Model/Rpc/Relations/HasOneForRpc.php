<?php

declare(strict_types=1);

namespace Ece2\Common\Model\Rpc\Relations;

use Ece2\Common\Model\Rpc\Model\Base;
use Hyperf\Database\Model\Relations\HasOne;
use Hyperf\Utils\Arr;

class HasOneForRpc extends HasOne
{
    /**
     * 重写 relation 获取关联数据.
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @return \Hyperf\Database\Model\Collection
     */
    public function getEager()
    {
        $builder = $this->applyScopes();

        /** @var Base $related */
        $related = $this->getRelated();
        // 整合关联条件数据, id 关联
        $idsCondition = (array) Arr::get($this->getBaseQuery()->wheres, '0.values', []);
        // 处理 N + 1 问题
        if (count($models = $related::get($idsCondition)) > 0) {
            $models = $builder->eagerLoadRelations($models);
        }

        return $builder->getModel()->newCollection($models);
    }

    /**
     * 重写 relation getAttribute 获取.
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @return null|Base|\Hyperf\Database\Model\Builder|\Hyperf\Database\Model\Model|object
     */
    public function getResults()
    {
        /** @var Base $related */
        $related = $this->getRelated();

        // 获取远端数据
        /** @var Base $result */
        $result = Arr::first(
            $related::get((array) Arr::get(
                $this->getBaseQuery()->wheres,
                '0.values',
                Arr::get($this->getBaseQuery()->wheres, '0.value', [])
            )),
            default: collect([])
        );

        return $related->setRawAttributes($result?->toArray(), true);
    }
}
