<?php

declare(strict_types=1);

namespace Ece2\Common\Model\Scopes;

use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Model;
use Hyperf\Database\Model\Scope;
use Hyperf\Utils\Context;

/**
 * 公司隔离
 */
class CompanyIsolateScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $builder->where($model->getQualifiedCompanyIdColumn(), static::getCompanyId());
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
        // 上下文里获取, admin 项目用的 jwt 会写入上下文, 其他项目可以手动写入匿名函数返回管理员数据
        try {
            if (($userResolver = Context::get('currentAdmin')) && is_callable($userResolver)) {
                $admin = $userResolver();
                if (isset($admin['company_id'])) {
                    return $admin['company_id'];
                }
            }
        } catch (\Exception $e) {
        }
        // 如果上下文没有, 允许设置全局公司 ID
        if (empty($companyId)) {
            return Context::get('GlobalCompanyId', 0);
        }

        return 0;
    }
}
