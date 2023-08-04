<?php

declare(strict_types=1);

namespace Ece2\Common\Model\Traits;

use Ece2\Common\Model\Rpc\Model\Area;

/**
 * @property Area $provinceInfo
 * @property Area $cityInfo
 * @property Area $districtInfo
 */
trait HasArea
{
    use HasRelationshipsForRpc;

    public function provinceInfo()
    {
        return $this->rpcHasOne(Area::class, 'id', $this->provinceColumnName ?: 'province');
    }

    public function cityInfo()
    {
        return $this->rpcHasOne(Area::class, 'id', $this->cityColumnName ?: 'city');
    }

    public function districtInfo()
    {
        return $this->rpcHasOne(Area::class, 'id', $this->districtColumnName ?: 'district');
    }

    /**
     * 全地址.
     * @return mixed|string
     */
    public function getFullAddress()
    {
        return ($this->provinceInfo['name'] ?? '') . ($this->cityInfo['name'] ?? '') . ($this->districtInfo['name'] ?? '');
    }
}
