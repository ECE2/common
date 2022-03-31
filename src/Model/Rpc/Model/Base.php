<?php

declare(strict_types=1);

namespace Ece2\Common\Model\Rpc\Model;

use Hyperf\DbConnection\Model\Model as BaseModel;
use Hyperf\Database\Model\Collection;

abstract class Base extends BaseModel
{
    protected $guarded = [];

    /**
     * @param mixed $params
     * @return \Hyperf\Database\Model\Collection|static[]
     */
    public static function get($params = [])
    {
        return new Collection();
    }
}
