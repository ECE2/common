<?php

declare(strict_types=1);

namespace Ece2\Common\Model\Rpc\Relations;

use Hyperf\Database\Model\Relations\MorphMany;

class MorphManyForRpc extends MorphMany
{
    public function delete()
    {
        // TODO
//        if (isset($this->onDelete)) {
//            return call_user_func($this->onDelete, $this);
//        }

        return $this->toBase()->delete();
    }
}
