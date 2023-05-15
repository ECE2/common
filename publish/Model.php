<?php

declare(strict_types=1);

namespace App\Model;

use Ece2\Common\Abstracts\AbstractModel as BaseModel;
use Hyperf\ModelCache\CacheableInterface;

abstract class Model extends BaseModel implements CacheableInterface
{
}
