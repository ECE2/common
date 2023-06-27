<?php

namespace Ece2\Common\Model\Traits;

use App\Model\CourierCompany;

trait HasCourierCompany
{
    use HasRelationshipsForRpc;

    /**
     * 快递公司.
     */
    public function courierCompany()
    {
        return $this->rpcHasOne(CourierCompany::class, 'id', $this->courierCompanyColumnName ?: 'courier_company_id');
    }
}
