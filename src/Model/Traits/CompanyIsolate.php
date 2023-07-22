<?php

declare(strict_types=1);

namespace Ece2\Common\Model\Traits;

use App\Model\Company;
use Ece2\Common\Model\Scopes\CompanyIsolateScope;
use Ece2\Common\Model\Rpc\Model\Company as CompanyForRpc;

/**
 * @method static static|\Hyperf\Database\Model\Builder|\Hyperf\Database\Query\Builder withCompanyIsolateExcept(bool $withCompanyIsolateExcept = true)
 * @method static static|\Hyperf\Database\Model\Builder|\Hyperf\Database\Query\Builder withoutCompanyIsolateExcept()
 */
trait CompanyIsolate
{
    use HasRelationshipsForRpc;

    /**
     * 注册 scope
     * @return void
     */
    public static function bootCompanyIsolate()
    {
        static::addGlobalScope(new CompanyIsolateScope());
    }

    public function creating($event)
    {
        $this->setCompanyId(CompanyIsolateScope::getCompanyId());
    }

    public function setCompanyId($value)
    {
        // 没有值时才设置, 有些场景, 比如 $company->dept()->create() 时, 会有 company_id 的设置, 这里就跳过自动设置
        if ($this->getAttribute($this->getCompanyIdColumn()) === null) {
            $this->{$this->getCompanyIdColumn()} = $value;
        }

        return $this;
    }

    public function getCompanyIdColumn()
    {
        return defined('static::COMPANY_ID') ? static::COMPANY_ID : 'company_id';
    }

    public function getQualifiedCompanyIdColumn()
    {
        return $this->qualifyColumn($this->getCompanyIdColumn());
    }

    /**
     * 所属公司.
     * @return \Hyperf\Database\Model\Relations\BelongsTo
     */
    public function company()
    {
        if (is_base_system()) {
            return $this->belongsTo(Company::class, $this->getCompanyIdColumn());
        } else {
            return $this->rpcBelongsTo(CompanyForRpc::class, $this->getCompanyIdColumn());
        }
    }
}
