<?php

declare(strict_types=1);

namespace Ece2\Common\Model\Rpc\Relations;

use Ece2\Common\Model\Rpc\Model\Base;
use Hyperf\Database\Model\Relations\HasOne;

class HasOneForRpc extends HasOne
{
    /**
     * 重写 model 获取关联数据, 改用 rpc 来获取.
     * @return Base[]|\Hyperf\Database\Model\Collection
     */
    public function getEager()
    {
        /** @var Base $related */
        $related = $this->getRelated();

        // 整合关联条件数据, id 关联
        return $related->newCollection($related::get(($this->getBaseQuery()->wheres[0] ?? [])['values'] ?? []));
    }
}
