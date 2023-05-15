<?php

declare(strict_types=1);

namespace Ece2\Common\Abstracts;

use App\Model\SystemDept;
use Ece2\Common\Exception\BusinessException;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Collection;
use Hyperf\Database\Model\Model;
use Hyperf\DbConnection\Annotation\Transactional;
use Hyperf\HttpServer\Response;
use Hyperf\Utils\ApplicationContext;
use Psr\Http\Message\ResponseInterface;

use function PHPUnit\Framework\throwException;
use function Hyperf\Support\make;

/**
 * @mixin Builder
 */
abstract class AbstractService
{
    /**
     * @var Model
     */
    public $model;

    public function __call(string $name, array $arguments)
    {
        return $this->model::query()->{$name}(...$arguments);
    }

    /**
     * 新增数据.
     * @param $data
     * @return Model
     */
    public function create($data)
    {
        $this->filterExecuteAttributes($data);

        return $this->model::create($data);
    }

    /**
     * 单个或批量从回收站恢复数据.
     * @return int
     */
    public function recovery(array $ids)
    {
        return $this->model::query()->whereIn($this->model->getKeyName(), $ids)->withTrashed()->restore();
    }

    /**
     * 从回收站读取数据.
     * @return null|Builder|Builder[]|Collection|Model
     */
    public function readByRecycle(array $id, array $columns = ['*'])
    {
        return $this->model::query()->withTrashed()->find($id, $columns);
    }

    /**
     * 单个或批量真实删除数据.
     * @return int
     */
    public function realDelete(array $ids)
    {
        // TODO withTrashed 验证下是不是可以不用加
        return $this->model::query()->whereIn($this->model->getKeyName(), $ids)->withTrashed()->forceDelete();
    }

    /**
     * 更新一条数据.
     * @param $id
     * @param $data
     * @return bool
     */
    public function update($id, $data)
    {
        $this->filterExecuteAttributes($data, true);

        return $this->model::query()->findOrFail($id)?->update($data);
    }

    /**
     * 按条件更新数据.
     * @return int
     */
    public function updateByCondition(array $condition, array $data)
    {
        $this->filterExecuteAttributes($data, true);

        return $this->model::query()->where($condition)->update($data);
    }

    /**
     * 修改数据状态
     * @param mixed $id
     * @param mixed $value
     * @param mixed $field
     * @return int
     */
    public function changeStatus(array $id, string $value, string $field = 'status')
    {
        return (string) $value === (string) $this->model::ENABLE ? $this->enable($id, $field) : $this->disable($id, $field);
    }

    /**
     * 单个或批量启用数据.
     * @return int
     */
    public function enable(array $ids, string $field = 'status')
    {
        return $this->model::query()->whereIn($this->model->getKeyName(), $ids)->update([$field => $this->model::ENABLE]);
    }

    /**
     * 单个或批量禁用数据.
     * @return int
     */
    public function disable(array $ids, string $field = 'status')
    {
        return $this->model::query()->whereIn($this->model->getKeyName(), $ids)->update([$field => $this->model::DISABLE]);
    }

    /**
     * 详情.
     * @param $id
     * @param $columns
     * @return Model
     */
    public function detail($id, $columns = ['*'])
    {
        return $this->findOrFail($id, $columns);
    }

    /**
     * 获取单个值.
     */
    public function value(array $condition, string $columns = 'id')
    {
        return $this->model::query()->where($condition)->value($columns);
    }

    /**
     * 获取单列值.
     * @return \Hyperf\Utils\Collection
     */
    public function pluck(array $condition, string $columns = 'id')
    {
        return $this->model::query()->where($condition)->pluck($columns);
    }

    /**
     * 搜索处理器.
     * @param $params
     */
    public function handleQueryPreProcessing(Builder $builder, $params): Builder
    {
        return $builder;
    }

    /**
     * 返回模型查询构造器.
     * @param null|array $params
     *                           + select
     *                           + recycle
     *                           + orderBy
     *                           + orderType
     * @return Builder
     */
    public function listQueryPreProcessing(?array $params, bool|array $dataPermission = true, callable $extend = null)
    {
        $params['select'] = array_values(array_filter(is_string($params['select'] ?? '') ? explode(',', $params['select'] ?? '') : (array) $params['select']));
        $query = $this->handleQueryPreProcessing($this->model::query(), $params);

        return tap(
            $query
                ->when($params['recycle'] ?? false, fn (Builder $builder) => $builder->onlyTrashed())
                ->when($params['select'], fn (Builder $builder, $select) => $builder->select($this->filterQueryAttributes($select)))
                ->when($dataPermission, fn (Builder $builder) => $builder->dataPermission(userId: $dataPermission['userId'] ?? null, initialUserIds: $dataPermission['initialUserIds'] ?? []))
                // 排序部分
                ->when($params['orderBy'] ?? false, function ($query) use ($params) {
                    if (is_array($params['orderBy'])) {
                        foreach ($params['orderBy'] as $key => $order) {
                            $query->orderBy($order, $params['orderType'][$key] ?? 'asc');
                        }
                    } else {
                        $query->orderBy($params['orderBy'], $params['orderType'] ?? 'asc');
                    }
                })
                // 时间倒序
                ->when(($params['orderByDescCreated'] ?? true) && $this->model?->timestamps, fn (Builder $builder) => $builder->orderByDesc($this->model->getCreatedAtColumn())),
            $extend ?? static fn (Builder $builder) => $builder
        );
    }

    /**
     * 获取列表数据.
     * @return Builder[]|Collection
     */
    public function getList(?array $params = null, bool|array $dataPermission = true, callable $extend = null)
    {
        return $this
            ->listQueryPreProcessing($params, $dataPermission, $extend)
            ->get();
    }

    /**
     * 获取列表数据.
     */
    public function getPageList(?array $params = null, bool|array $dataPermission = true, callable $extend = null)
    {
        return $this
            ->listQueryPreProcessing($params, $dataPermission, $extend)
            ->paginate();
    }

    /**
     * 获取树列表.
     * @return array
     */
    public function getTreeList(
        ?array $params = null,
        bool|array $dataPermission = true,
        callable $extend = null,
        string $idField = 'id',
        string $parentField = 'parent_id',
        string $childrenField = 'children'
    ) {
        return array_to_tree(
            $this
                ->listQueryPreProcessing($params, $dataPermission, $extend)
                ->get()
                ->toArray(),
            $idField,
            $parentField,
            $childrenField
        );
    }

    /**
     * 过滤新增或写入不存在的字段.
     */
    public function filterExecuteAttributes(array &$data, bool $removePk = false)
    {
        $attrs = $this->model->getFillable();
        foreach ($data as $name => $val) {
            if (! in_array($name, $attrs)) {
                unset($data[$name]);
            }
        }

        if ($removePk && isset($data[$this->model->getKeyName()])) {
            unset($data[$this->model->getKeyName()]);
        }
    }

    /**
     * 导出数据.
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function export(array $params, ?string $dto, string $filename = null, callable $extend = null): ResponseInterface
    {
        if (empty($dto)) {
            throw new \Exception('导出未指定DTO');
        }

        if (empty($filename)) {
            $filename = $this->model->getTable();
        }

        return make(Collection::class)->export($dto, $filename, $this->getList($params, extend: $extend)->toArray());
    }

    /**
     * 数据导入.
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @return bool
     */
    #[Transactional]
    public function import(string $dto, ?\Closure $closure = null)
    {
        return collection_import($dto, $this->model, $closure);
    }

    /**
     * 读取列表时，通过创建者获取对应的部门信息.
     */
    public static function handleDeptFromCreatedByForList(): \Closure
    {
        return fn (Builder $builder) => $builder->when(
            identity()->isSuperAdmin(),
            fn (Builder $builder) => $builder->with('createdByInstance.department')
        );
    }

    /**
     * 自动标记数据记录的创建者（created_by）信息.
     * @param $data
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function handleCreatedByFromDeptForAE($data)
    {
        if (! method_exists($this->model, 'getCreatedByColumn')) {
            return $data;
        }

        $createdByColumn = $this->model->getCreatedByColumn();
        // 如果 数据为空 或 如果未选择部门 或 已经有创建人
        if (empty($data) || empty($data['dept_id']) || ! empty($data[$createdByColumn])) {
            return $data;
        }

        // 如果是超管角色, 则需要判断部门 ID 信息的合法性
        if (identity()->isSuperAdmin()) {
            // 判断部门是否存在
            $dept = is_base_system() ?
                SystemDept::find($data['dept_id'])?->topLevelDept()?->toArray() :
                (new \Ece2\Common\Model\Rpc\Model\SystemDept(['id' => $data['dept_id']]))->topLevelDept()?->toArray();
            if (empty($dept)) {
                throw new BusinessException(message: '部门不存在');
            }

            // 如果部门有创始人, 则把当前记录的创建人改成部门的负责人
            if (! empty($dept[$createdByColumn])) {
                $data[$createdByColumn] = $dept[$createdByColumn];
            }
        }

        return $data;
    }

    /**
     * 过滤查询字段不存在的属性.
     * @return array
     */
    protected function filterQueryAttributes(array $fields, bool $removePk = false)
    {
        $attrs = $this->model->getFillable();
        foreach ($fields as $key => $field) {
            if (! in_array(trim($field), $attrs)) {
                unset($fields[$key]);
            } else {
                $fields[$key] = trim($field);
            }
        }

        if ($removePk && in_array($this->model->getKeyName(), $fields)) {
            unset($fields[array_search($this->model->getKeyName(), $fields)]);
        }

        return (count($fields) < 1) ? ['*'] : array_values($fields);
    }

    /**
     * 数字更新操作
     * @param int $id
     * @param string $field
     * @param int $value
     * @return bool
     */
    public function numberOperation(int $id, string $field, int $value): bool
    {
        return $this->update($id, [ $field => $value]);
    }
}
