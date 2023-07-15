<?php

declare(strict_types=1);

namespace Ece2\Common\Model\Rpc\Relations;

use Ece2\Common\Model\Rpc\Model\Base;
use Hyperf\Database\Model\Relations\BelongsToMany;

class BelongToManyForRpc extends BelongsToMany
{
    /**
     * 重写 model 获取关联数据, 改用 rpc 来获取.
     * @return Base[]|\Hyperf\Database\Model\Collection
     */
    public function getEager()
    {
        // TODO
    }
}
