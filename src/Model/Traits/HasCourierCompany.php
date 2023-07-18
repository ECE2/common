<?php

namespace Ece2\Common\Model\Traits;

use App\Model\CourierCompany;
use Ece2\Common\Model\Rpc\Model\CourierCompany as CourierCompanyRpc;

trait HasCourierCompany
{
    use HasRelationshipsForRpc;

    /**
     * 快递公司.
     */
    public function courierCompany()
    {
        if (is_base_system()) {
            return $this->hasOne(CourierCompany::class, 'id', $this->courierCompanyColumnName ?: 'courier_company_id');
        } else {
            return $this->rpcHasOne(CourierCompanyRpc::class, 'id', $this->courierCompanyColumnName ?: 'courier_company_id');
        }
    }
}
