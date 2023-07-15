<?php

declare(strict_types=1);

namespace Ece2\Common\Model\Rpc\Relations;

use Ece2\Common\Model\Rpc\Model\Base;
use Hyperf\Collection\Arr;
use Hyperf\Database\Model\Relations\BelongsTo;
use Hyperf\Database\Model\Relations\BelongsToMany;
use function Hyperf\Collection\collect;

class BelongToForRpc extends BelongsTo
{
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
        $result = Arr::first($related::get((array) Arr::get($this->getBaseQuery()->wheres, '0.value')));

        return $related->setRawAttributes($result?->toArray(), true);
    }
}
