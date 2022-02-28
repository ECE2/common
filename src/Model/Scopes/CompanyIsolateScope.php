<?php

declare(strict_types=1);
/**
 * This file is part of api template.
 */
namespace Ece2\HyperfCommon\Model\Scopes;

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
        $admin = [];
        try {
            if (($userResolver = Context::get('userResolver')) && is_callable($userResolver)) {
                $admin = $userResolver();
            }
        } catch (\Exception $e) {
        }

        $builder->where($model->getTable() . '.company_id', $admin['company_id'] ?? 0);
    }
}
