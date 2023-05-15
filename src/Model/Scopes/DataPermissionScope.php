<?php

declare(strict_types=1);

namespace Ece2\Common\Model\Scopes;

use App\Model\SystemDept;
use App\Model\User;
use Ece2\Common\Exception\HttpException;
use Ece2\Common\JsonRpc\Contract\SystemDeptServiceInterface;
use Ece2\Common\JsonRpc\Contract\SystemUserServiceInterface;
use Ece2\Common\Model\Rpc\Model\SystemRole;
use Ece2\Common\Model\Rpc\Model\User as UserForRpc;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Model;
use Hyperf\Database\Model\Scope;

/**
 * 数据权限.
 */
class DataPermissionScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        return $builder;
    }

    public function extend(Builder $builder)
    {
        /*
         * 数据权限范围.
         * @params int|null $userId 某用户的权限范围
         * @params array $initialUserIds 初始用户有的权限范围
         */
        $builder->macro('dataPermission', function (Builder $builder, ?int $userId = null, array $initialUserIds = []) {
            $userId = $userId ?? (int) identity()?->getKey();
            if (empty($userId)) {
                throw new HttpException(message: 'Data Scope missing user_id');
            }

            // 没有创建人字段
            $hasNotCreatedByColumn = ! method_exists($builder->getModel(), 'getCreatedByColumn');
            if ($hasNotCreatedByColumn) {
                return $builder;
            }

            $dataScope = new class($builder, $userId, $initialUserIds) {
                /**
                 * 数据范围用户 ID 列表.
                 */
                protected array $userIds = [];

                public function __construct(
                    protected Builder $builder,
                    protected int $userId,
                    protected array $initialUserIds = []
                ) {
                }

                public function execute(): Builder
                {
                    // 计算统计权限范围
                    $this->getUserDataScope();
                    // 如果空 (侧面说明有所有权限) 则跳出 不处理
                    if (empty($this->userIds)) {
                        return $this->builder;
                    }

                    // 加上提交的 初始有权限的范围
                    $this->userIds = array_merge($this->userIds, $this->initialUserIds);

                    return $this->builder->whereIn($this->builder->getModel()->getCreatedByColumn(), array_unique($this->userIds));
                }

                protected function getUserDataScope(): void
                {
                    /* @var UserForRpc|User $user */
                    if (is_base_system()) {
                        $user = User::findOrFail($this->userId);
                        $roles = $user->roles()->get(['id', 'data_scope']);
                    } else {
                        $user = (new UserForRpc(
                            container()->get(SystemUserServiceInterface::class)
                                ->getInfo($this->userId)['data']['user'] ?? []
                        ));
                        $roles = $user->getRoles();
                    }
                    // 超管
                    if ($user->isSuperAdmin()) {
                        $this->userIds = [];
                        return;
                    }
                    // 没有角色的情况下, 默认只能看自己的
                    if ($roles->isEmpty()) {
                        $this->userIds[] = $this->userId;
                        return;
                    }

                    foreach ($roles as $role) {
                        switch ((int) $role->data_scope) {
                            // 如果是所有权限，跳出所有循环
                            case 1: // SystemRole::ALL_SCOPE
                                $this->userIds = [];
                                break 2;
                            // 自定义数据权限
                            case 2: // SystemRole::CUSTOM_SCOPE
                                if (is_base_system()) {
                                    $deptIds = $role->depts()->pluck('id')->toArray();
                                    $this->userIds = array_merge(
                                        $this->userIds,
                                        User::query()->whereIn('dept_id', $deptIds)->pluck('id')->toArray()
                                    );
                                } else {
                                    /** @var SystemRole $role */
                                    $deptIds = array_column($role->getDepts(), 'id');
                                    $this->userIds = array_merge(
                                        $this->userIds,
                                        array_column(container()->get(SystemUserServiceInterface::class)->getByDeptIds($deptIds)['data'] ?? [], 'id')
                                    );
                                }

                                $this->userIds[] = $this->userId;
                                break;
                            // 本部门数据权限
                            case 3: // SystemRole::SELF_DEPT_SCOPE
                                if (is_base_system()) {
                                    $this->userIds = array_merge(
                                        $this->userIds,
                                        User::query()->where('dept_id', $user['dept_id'])->pluck('id')->toArray()
                                    );
                                } else {
                                    $this->userIds = array_merge(
                                        $this->userIds,
                                        array_column(container()->get(SystemUserServiceInterface::class)->getByDeptIds([$user['dept_id']])['data'] ?? [], 'id')
                                    );
                                }

                                $this->userIds[] = $this->userId;
                                break;
                            // 本部门及子部门数据权限
                            case 4: // SystemRole::DEPT_BELOW_SCOPE
                                $this->userIds = array_merge($this->userIds, $this->getDeptUser($user['dept_id']));
                                $this->userIds[] = $this->userId;
                                break;
                            // 自己的数据
                            case 5: // SystemRole::SELF_SCOPE
                                $this->userIds[] = $this->userId;
//                            // 全公司 (顶级部门下 包含所有子部门)
//                            // no break
//                            case 6: // SystemRole::COMPANY_SCOPE
//                                // 找到当前部门的顶级部门
//                                if (is_base_system()) {
//                                    $topLevelDept = $user->department?->topLevelDept();
//                                } else {
//                                    $topLevelDept = (new \Ece2\Common\Model\Rpc\Model\SystemDept(['id' => $user['dept_id']]))?->topLevelDept();
//                                }
//
//                                $this->userIds = array_merge($this->userIds, $topLevelDept !== null ? $this->getDeptUser($topLevelDept->getKey()) : []);
//                                $this->userIds[] = $this->userId;
                                // no break
                            default:
                                break;
                        }
                    }
                }

                /**
                 * 获取部门用户.
                 * @param $deptId
                 * @return array|mixed[]
                 */
                protected function getDeptUser($deptId)
                {
                    if (is_base_system()) {
                        $deptIds = SystemDept::query()->whereRaw("FIND_IN_SET(?, level)", [$deptId])->pluck('id')->toArray();
                        $deptIds[] = $deptId;
                        return User::query()->whereIn('dept_id', $deptIds)->pluck('id')->toArray();
                    }
                    $deptIds = array_column(container()->get(SystemDeptServiceInterface::class)->getByLevelFuzzy($deptId)['data'] ?? [], 'id');
                    $deptIds[] = $deptId;

                    return ! empty($deptIds) ? array_column(container()->get(SystemUserServiceInterface::class)->getByDeptIds($deptIds)['data'] ?? [], 'id') : [];
                }
            };

            return $dataScope->execute();
        });
    }
}
