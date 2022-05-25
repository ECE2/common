<?php

declare(strict_types=1);

namespace Ece2\Common\Abstracts;

use Ece2\Common\Annotation\Transaction;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Collection;
use Hyperf\Database\Model\Model;
use Hyperf\HttpServer\Response;
use Hyperf\Paginator\Paginator;
use Hyperf\Utils\ApplicationContext;
use Psr\Http\Message\ResponseInterface;

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
     */
    public function create($data): Model
    {
        $this->filterExecuteAttributes($data);

        return $this->model::create($data);
    }

    /**
     * 单个或批量软删除数据.
     */
    public function destroy(array $ids): int
    {
        return $this->model::destroy($ids);
    }

    /**
     * 单个或批量从回收站恢复数据.
     */
    public function recovery(array $ids): int
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
     */
    public function realDelete(array $ids): int
    {
        // TODO withTrashed 验证下是不是可以不用加
        return $this->model::query()->whereIn($this->model->getKeyName(), $ids)->withTrashed()->forceDelete();
    }

    /**
     * 更新一条数据.
     * @param $id
     * @param $data
     */
    public function update($id, $data): bool
    {
        $this->filterExecuteAttributes($data, true);

        return $this->model::query()->findOrFail($id)?->update($data);
    }

    /**
     * 按条件更新数据.
     */
    public function updateByCondition(array $condition, array $data): int
    {
        $this->filterExecuteAttributes($data, true);

        return $this->model::query()->where($condition)->update($data);
    }

    /**
     * 修改数据状态
     * @param mixed $id
     * @param mixed $value
     * @param mixed $field
     */
    public function changeStatus(array $id, string $value, string $field = 'status'): int
    {
        return (string) $value === (string) $this->model::ENABLE ? $this->enable($id, $field) : $this->disable($id, $field);
    }

    /**
     * 单个或批量启用数据.
     */
    public function enable(array $ids, string $field = 'status'): int
    {
        return $this->model::query()->whereIn($this->model->getKeyName(), $ids)->update([$field => $this->model::ENABLE]);
    }

    /**
     * 单个或批量禁用数据.
     */
    public function disable(array $ids, string $field = 'status'): int
    {
        return $this->model::query()->whereIn($this->model->getKeyName(), $ids)->update([$field => $this->model::DISABLE]);
    }

    /**
     * 详情.
     * @param $id
     * @param $columns
     * @return mixed
     */
    public function detail($id, $columns = ['*'])
    {
        return $this->find($id, $columns);
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
     */
    public function pluck(array $condition, string $columns = 'id'): \Hyperf\Utils\Collection
    {
        return $this->model::query()->where($condition)->pluck($columns);
    }

    /**
     * 搜索处理器
     * @param Builder $builder
     * @param $params
     * @return Builder
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
    public function listQueryPreProcessing(?array $params, bool $dataPermission = true, callable $extend = null)
    {
        $params['select'] = array_values(array_filter(is_string($params['select'] ?? '') ? explode(',', $params['select'] ?? '') : (array) $params['select']));
        $query = $this->handleQueryPreProcessing($this->model::query(), $params);

        return tap(
            $query
                ->when($params['recycle'] ?? false, fn (Builder $builder) => $builder->onlyTrashed())
                ->when($params['select'], fn (Builder $builder, $select) => $builder->select($this->filterQueryAttributes($select)))
                ->when($dataPermission, fn (Builder $builder) => $builder->dataPermission())
                // 排序部分
                ->when($params['orderBy'] ?? false, function ($query) use ($params) {
                    if (is_array($params['orderBy'])) {
                        foreach ($params['orderBy'] as $key => $order) {
                            $query->orderBy($order, $params['orderType'][$key] ?? 'asc');
                        }
                    } else {
                        $query->orderBy($params['orderBy'], $params['orderType'] ?? 'asc');
                    }
                }),
            $extend ?? static fn (Builder $builder) => $builder
        );
    }

    /**
     * 获取列表数据.
     * @return Builder[]|Collection
     */
    public function getList(?array $params = null, bool $dataPermission = true, callable $extend = null)
    {
        return $this
            ->listQueryPreProcessing($params, $dataPermission, $extend)
            ->get();
    }

    /**
     * 获取列表数据.
     */
    public function getPageList(?array $params = null, bool $dataPermission = true, callable $extend = null)
    {
        return $this
            ->listQueryPreProcessing($params, $dataPermission, $extend)
            ->paginate();
    }

    /**
     * 获取树列表.
     */
    public function getTreeList(
        ?array $params = null,
        bool $dataPermission = true,
        callable $extend = null,
        string $idField = 'id',
        string $parentField = 'parent_id',
        string $childrenField = 'children'
    ) : array
    {
        return $this
            ->listQueryPreProcessing($params, $dataPermission, $extend)
            ->get()
            ->toTree($idField, $parentField, $childrenField);
    }

    /**
     * 过滤新增或写入不存在的字段.
     */
    public function filterExecuteAttributes(array &$data, bool $removePk = false): void
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
     * 过滤查询字段不存在的属性.
     */
    protected function filterQueryAttributes(array $fields, bool $removePk = false): array
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
     * 导出数据.
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function export(array $params, ?string $dto, string $filename = null, callable $extend = null): ResponseInterface
    {
        if (empty($dto)) {
            return ApplicationContext::getContainer()->get(Response::class)->error('导出未指定DTO');
        }

        if (empty($filename)) {
            $filename = $this->model->getTable();
        }

        return make(Collection::class)->export($dto, $filename, $this->getList($params, extend: $extend)->toArray());
    }

    /**
     * 数据导入.
     * @Transaction
     */
    public function import(string $dto, ?\Closure $closure = null): bool
    {
        return make(Collection::class)->import($dto, $this->model, $closure);
    }
}
