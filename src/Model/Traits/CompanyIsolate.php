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

    /**
     *
     * @return bool
     */
    public function touch(): bool
    {
        $this->updateCompanyId();

        return $this->save();
    }

    public function updateCompanyId()
    {
        if (! $this->exists && ! $this->isDirty($this->getCompanyIdColumn())) {
            $this->setCompanyId(CompanyIsolateScope::getCompanyId());
        }
    }

    public function setCompanyId($value)
    {
        $this->{$this->getCompanyIdColumn()} = $value;

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
