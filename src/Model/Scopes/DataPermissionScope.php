<?php

declare(strict_types=1);

namespace Ece2\Common\Model\Scopes;

use App\Model\SystemDept;
use App\Model\SystemUser;
use Ece2\Common\Exception\HttpException;
use Ece2\Common\JsonRpc\Contract\SystemDeptServiceInterface;
use Ece2\Common\JsonRpc\Contract\SystemUserServiceInterface;
use Ece2\Common\Model\Rpc\Model\SystemRole;
use Ece2\Common\Model\Rpc\Model\SystemUser as SystemUserForRpc;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Model;
use Hyperf\Database\Model\Scope;
use Hyperf\Utils\Context;

/**
 * 数据权限
 */
class DataPermissionScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        return $builder;
    }

    public function extend(Builder $builder)
    {
        $builder->macro('dataPermission', function (Builder $builder, ?int $userId = null) {
            $userId = $userId ?? (int) identity()?->getKey();
            if (empty($userId)) {
                throw new HttpException(message: 'Data Scope missing user_id');
            }

            if ($userId == config('config_center.system.super_admin', env('SUPER_ADMIN'))
                || !method_exists($builder->getModel(), 'getCreatedByColumn')) {
                return $builder;
            }

            $dataScope = new class($userId, $builder) {
                // 数据范围用户ID列表
                protected array $userIds = [];

                public function __construct(protected int $userid, protected Builder $builder)
                {
                }

                public function execute(): Builder
                {
                    $this->getUserDataScope();
                    if (empty($this->userIds)) {
                        return $this->builder;
                    } else {
                        $this->userIds[] = $this->userid;
                        return $this->builder->whereIn('created_by', array_unique($this->userIds));
                    }
                }

                protected function getUserDataScope(): void
                {
                    if (is_base_system()) {
                        $user = SystemUser::findOrFail($this->userid, ['id', 'dept_id']);
                        $roles = $user->roles()->get(['id', 'data_scope']);
                    } else {
                        /** @var SystemUserForRpc $user */
                        $user = (new SystemUserForRpc(container()->get(SystemUserServiceInterface::class)->getInfo($this->userid)['data']['user'] ?? []));
                        $roles = $user->getRoles();
                    }

                    foreach ($roles as $role) {
                        switch ((int) $role->data_scope) {
                            case 0: // SystemRole::ALL_SCOPE
                                // 如果是所有权限，跳出所有循环
                                break 2;
                            case 1: // SystemRole::CUSTOM_SCOPE
                                // 自定义数据权限
                                if (is_base_system()) {
                                    $deptIds = $role->depts()->pluck('id')->toArray();
                                    $this->userIds = array_merge(
                                        $this->userIds,
                                        SystemUser::query()->whereIn('dept_id', $deptIds)->pluck('id')->toArray()
                                    );
                                } else {
                                    /** @var SystemRole $role */
                                    $deptIds = array_column($role->getDepts(), 'id');
                                    $this->userIds = array_merge(
                                        $this->userIds,
                                        array_column(container()->get(SystemUserServiceInterface::class)->getByDeptIds($deptIds)['data'] ?? [], 'id')
                                    );
                                }

                                break;
                            case 2: // SystemRole::SELF_DEPT_SCOPE
                                // 本部门数据权限
                                if (is_base_system()) {
                                    $this->userIds = array_merge(
                                        $this->userIds,
                                        SystemUser::query()->where('dept_id', $user['dept_id'])->pluck('id')->toArray()
                                    );
                                } else {
                                    $this->userIds = array_merge(
                                        $this->userIds,
                                        array_column(container()->get(SystemUserServiceInterface::class)->getByDeptIds([$user['dept_id']])['data'] ?? [], 'id')
                                    );
                                }
                                break;
                            case 3: // SystemRole::DEPT_BELOW_SCOPE
                                // 本部门及子部门数据权限
                                if (is_base_system()) {
                                    $deptIds = SystemDept::query()->where('level', 'like', '%' . $user['dept_id'] . '%')->pluck('id')->toArray();
                                    $deptIds[] = $user['dept_id'];
                                    $this->userIds = array_merge(
                                        $this->userIds,
                                        SystemUser::query()->whereIn('dept_id', $deptIds)->pluck('id')->toArray()
                                    );
                                } else {
                                    $deptIds = array_column(container()->get(SystemDeptServiceInterface::class)->getByLevelFuzzy($user['dept_id'])['data'] ?? [], 'id');
                                    $deptIds[] = $user['dept_id'];
                                    $this->userIds = array_merge(
                                        $this->userIds,
                                        !empty($deptIds) ?
                                            array_column(
                                                container()->get(SystemUserServiceInterface::class)->getByDeptIds($deptIds)['data'] ?? [],
                                                'id'
                                            ) : []
                                    );
                                }
                                break;
                            case 4: // SystemRole::SELF_SCOPE
                            default:
                                break;
                        }
                    }
                }
            };

            return $dataScope->execute();
        });
    }
}
