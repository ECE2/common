<?php

declare(strict_types=1);

namespace Ece2\Common\Model\Traits;

use Ece2\Common\Model\Scopes\CompanyIsolateScope;

/**
 * @method static static|\Hyperf\Database\Model\Builder|\Hyperf\Database\Query\Builder withCompanyIsolateExcept(bool $withCompanyIsolateExcept = true)
 * @method static static|\Hyperf\Database\Model\Builder|\Hyperf\Database\Query\Builder withoutCompanyIsolateExcept()
 */
trait CompanyIsolate
{
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
        return $this->qualifyColumn($this->getDeletedAtColumn());
    }
}
