<?php

declare(strict_types=1);
/**
 * This file is part of api template.
 */
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
        // 上下文里获取, admin 项目用的 jwt 会写入上下文, 其他项目可以手动写入匿名函数返回管理员数据
        $companyId = [];
        try {
            if (($userResolver = Context::get('userResolver')) && is_callable($userResolver)) {
                $admin = $userResolver();
                if (isset($admin['company_id'])) {
                    $companyId = [$admin['company_id']];
                }
            }
        } catch (\Exception $e) {
        }
        // 如果上下文没有, 允许设置全局公司 ID
        if (empty($companyId)) {
            $companyId = Context::get('GlobalCompanyId', [0]);
        }

        $builder->whereIn($model->getTable() . '.company_id', $companyId);
    }
}
