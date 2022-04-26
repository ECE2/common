<?php

declare(strict_types=1);

namespace Ece2\Common\Abstracts;

use App\Model\SystemDept;
use App\Model\SystemUser;
use Ece2\Common\Collection;
use Ece2\Common\Exception\HttpException;
use Ece2\Common\JsonRpc\Contract\SystemDeptServiceInterface;
use Ece2\Common\JsonRpc\Contract\SystemUserServiceInterface;
use Ece2\Common\Model\Rpc\Model\SystemRole;
use Ece2\Common\Model\Rpc\Model\SystemUser as SystemUserForRpc;
use Ece2\Common\Model\Traits\HasRelationshipsForRpc;
use Hyperf\Database\Model\Builder;
use Hyperf\DbConnection\Model\Model as BaseModel;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\ModelCache\Cacheable;

abstract class AbstractModel extends BaseModel
{
    use HasRelationshipsForRpc;
    use Cacheable;

    /**
     * 隐藏的字段列表
     * @var string[]
     */
    protected $hidden = ['deleted_at'];

    /**
     * 状态: 可用
     */
    public const ENABLE = '0';

    /**
     * 状态: 不可用
     */
    public const DISABLE = '1';
    
    /**
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // 注册用户数据权限方法
        $this->registerUserDataScope();
    }

    /**
     * 允许前端传长度.
     */
    public function getPerPage(): int
    {
        /** @var RequestInterface $request */
        $request = container()->get(RequestInterface::class);

        return (int) $request->input('pageSize', parent::getPerPage());
    }

    /**
     * 设置主键的值
     * @param string | int $value
     */
    public function setPrimaryKeyValue($value): void
    {
        $this->{$this->primaryKey} = $value;
    }

    /**
     * @return string
     */
    public function getPrimaryKeyType(): string
    {
        return $this->keyType;
    }

    /**
     * @param array $options
     * @return bool
     */
    public function save(array $options = []): bool
    {
        return parent::save($options);
    }

    /**
     * @param array $attributes
     * @param array $options
     * @return bool
     */
    public function update(array $attributes = [], array $options = []): bool
    {
        return parent::update($attributes, $options);
    }

    /**
     * @param array $models
     * @return Collection
     */
    public function newCollection(array $models = []): Collection
    {
        return new Collection($models);
    }

    /**
     * 注册自定义方法
     */
    private function registerUserDataScope()
    {
        // 数据权限方法
        $model = $this;
        Builder::macro('userDataScope', function (?int $userid = null) use ($model) {
            $userid = $userid ?? (int) identity()?->getKey();

            if (empty($userid)) {
                throw new HttpException(message: 'Data Scope missing user_id');
            }

            /* @var Builder $this */
            if ($userid == config('config_center.system.super_admin', env('SUPER_ADMIN'))) {
                return $this;
            }

            if (!in_array('created_by', $model->getFillable())) {
                return $this;
            }

            $dataScope = new class($userid, $this) {
                // 用户ID
                protected int $userid;

                // 查询构造器
                protected Builder $builder;

                // 数据范围用户ID列表
                protected array $userIds = [];

                public function __construct(int $userid, Builder $builder)
                {
                    $this->userid = $userid;
                    $this->builder = $builder;
                }

                /**
                 * @return Builder
                 */
                public function execute(): Builder
                {
                    $this->getUserDataScope();
                    if (empty($this->userIds)) {
                        return $this->builder;
                    } else {
                        array_push($this->userIds, $this->userid);
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
