<?php

declare(strict_types=1);

namespace Ece2\Common\Model\Scopes;

use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Model;
use Hyperf\Database\Model\Scope;
use Hyperf\Context\Context;
use Phalcon\Helper\Arr;

/**
 * 公司隔离 (用于多租户系统).
 */
class CompanyIsolateScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        // RPC 请求下, 不使用公司隔离
        if (!empty(container()->get(\Hyperf\Rpc\Context::class)->getData())) {
            return;
        }

        // 超管无视公司隔离 && 使用超管身份
        $identity = identity();
        $useSuperAdmin = context_get('useSuperAdmin');
        if ($identity && method_exists($identity, 'isSuperAdmin') && $identity->isSuperAdmin() && $useSuperAdmin) {
            return;
        }

        $builder->when(
            static::getCompanyId(),
            fn ($query, $companyId) => $query->where($model->getQualifiedCompanyIdColumn(), $companyId)
        );
    }

    public function extend(Builder $builder)
    {
        // 去掉公司隔离
        $builder->macro('withCompanyIsolateExcept', function (Builder $builder, $withCompanyIsolateExcept = true) {
            if (! $withCompanyIsolateExcept) {
                return $builder->withoutCompanyIsolateExcept();
            }

            return $builder->withoutGlobalScope($this);
        });

        // 公司隔离
        $builder->macro('withoutCompanyIsolateExcept', function (Builder $builder) {
            $model = $builder->getModel();

            $builder
                ->withoutGlobalScope($this)
                ->where($model->getQualifiedCompanyIdColumn(), static::getCompanyId());

            return $builder;
        });
    }

    public static function getCompanyId()
    {
        // 优先使用 设置的全局公司 ID
        if (! empty($companyId = Context::get('GlobalCompanyId', 0))) {
            return $companyId;
        }

        // 上下文里获取
        try {
            return company()?->getKey() ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }
}
