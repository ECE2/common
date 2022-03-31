<?php

declare(strict_types=1);

namespace Ece2\Common\Model\Rpc\Relations;

use Ece2\Common\Model\Rpc\Model\Base;
use Hyperf\Database\Model\Model;
use Hyperf\Database\Model\Relations\MorphToMany;
use Hyperf\Database\Query\JoinClause;

class MorphToManyForRpc extends MorphToMany
{
    /**
     * 重写 model 获取关联数据, 改用 rpc 来获取.
     * @return Base[]|\Hyperf\Database\Model\Collection
     */
    public function getEager()
    {
        // 先获取中间表数据
        $baseQuery = clone $this->getBaseQuery();
        /** @var JoinClause $join */
        $pivotJoin = $baseQuery->joins[0];
        $baseQuery->joins = null;
        $pivotResult = []; // 关联 ID 作为 key
        foreach ($baseQuery->from($pivotJoin->table)->select($this->aliasedPivotColumns())->get() as $item) {
            $item = (array) $item;
            $pivotResult[$item['pivot_' . $this->relatedPivotKey] ?? 0] = $item;
        }

        // 整合关联条件数据, ID 关联, RPC 查询
        $models = $this->related::get(array_column($pivotResult, 'pivot_' . $this->relatedPivotKey));
        /** @var Model $model */
        // 拼接数据, 加上 pivot
        foreach ($models as $model) {
            $model->setRawAttributes(array_merge($model->getAttributes(), $pivotResult[$model['id']] ?? []), true);
        }
        $this->hydratePivotRelation($models);

        // TODO N+1

        return $this->related->newCollection($models);
    }
}
