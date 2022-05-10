<?php

declare(strict_types=1);

namespace Ece2\Common\Abstracts;

use Ece2\Common\Annotation\Transaction;
use Ece2\Common\Collection;
use Ece2\Common\Response;
use Ece2\Common\Traits\ServiceTrait;
use Hyperf\Context\Context;
use Hyperf\Contract\LengthAwarePaginatorInterface;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Collection as ModelCollection;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Paginator\Paginator;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Arr;
use Psr\Http\Message\ResponseInterface;
use function Swoole\Coroutine\Http\request;

abstract class AbstractService
{
    /**
     * @var AbstractModel
     */
    public $model;

    /**
     * 获取列表数据.
     * @param array|null $params
     * @param bool $dataPermission
     * @param callable|null $extend
     * @return Builder[]|ModelCollection
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
    public function getPageList(?array $params = null, bool $dataPermission = true, string $pageName = 'page', callable $extend = null)
    {
        return $this
            ->listQueryPreProcessing($params, $dataPermission, $extend)
            ->paginate(
                perPage: (int) ($params['pageSize'] ?? $this->model::getModel()->getPerPage()),
                pageName: $pageName,
                page: (int) ($params[$pageName] ?? Paginator::resolveCurrentPage($pageName))
            );
    }

    /**
     * 返回模型查询构造器
     * @param array|null $params
     * + select
     * + recycle
     * + _mainAdmin_tree
     * + _mainAdmin_tree_pid
     * + orderBy
     * + orderType
     * @param bool $dataPermission
     * @return Builder
     */
    public function listQueryPreProcessing(?array $params, bool $dataPermission = true, callable $extend = null)
    {
        $params['select'] = array_values(array_filter(is_string($params['select'] ?? '') ? explode(',', $params['select'] ?? '') : []));

        return tap(
            $this->model::query()
                ->when($params['recycle'] ?? false, fn (Builder $builder) => $builder->onlyTrashed())
                ->when($params['select'], fn (Builder $builder, $select) => $builder->select($this->filterQueryAttributes($select)))
                ->when($dataPermission, fn (Builder $builder) => $builder->dataPermission())
                // 排序部分
                ->when(isset($params['_mainAdmin_tree']), fn($query) => $query->orderBy($params['_mainAdmin_tree_pid'])) // 对树型数据强行加个排序
                ->when($params['orderBy'] ?? false, function ($query) use ($params) {
                    if (is_array($params['orderBy'])) {
                        foreach ($params['orderBy'] as $key => $order) {
                            $query->orderBy($order, $params['orderType'][$key] ?? 'asc');
                        }
                    } else {
                        $query->orderBy($params['orderBy'], $params['orderType'] ?? 'asc');
                    }
                }),
            $extend ?? fn (Builder $builder) => $builder
        );
    }

    /**
     * 过滤查询字段不存在的属性
     * @param array $fields
     * @param bool $removePk
     * @return array
     */
    protected function filterQueryAttributes(array $fields, bool $removePk = false): array
    {
        $attrs = $this->model->getFillable();
        foreach ($fields as $key => $field) {
            if (!in_array(trim($field), $attrs)) {
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
     * 获取树列表.
     */
    public function getTreeList(
        ?array $params = null,
        bool $dataPermission = true,
        callable $extend = null,
        string $id = 'id',
        string $parentField = 'parent_id',
        string $children = 'children'
    ): array
    {
        $params['_mainAdmin_tree'] = true;
        $params['_mainAdmin_tree_pid'] = $parentField;
        $data = $this->listQueryPreProcessing($params, $dataPermission, $extend)->get();

        return $data->toTree(parentId: $data[0]?->{$parentField} ?? 0, id: $id, parentField: $parentField, children: $children);
    }

    /**
     * 新增数据.
     */
    public function create(array $data): int|string
    {
        $this->filterExecuteAttributes($data);
        $model = $this->model::create($data);

        return $model->{$model->getKeyName()};
    }

    /**
     * 批量新增.
     */
    public function batchSave(array $collects): bool
    {
        foreach ($collects as $collect) {
            $this->create($collect);
        }

        return true;
    }

    /**
     * 读取一条数据.
     * @deprecated 可以统一处理
     */
    public function find(int $id, array $column = ['*']): ?AbstractModel
    {
        return $this->model::find($id, $column);
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
    public function pluck(array $condition, string $columns = 'id'): array
    {
        return $this->model::query()->where($condition)->pluck($columns)->toArray();
    }

    /**
     * 从回收站读取一条数据.
     */
    public function readByRecycle(int $id): AbstractModel
    {
        return $this->model::query()->withTrashed()->find($id);
    }

    /**
     * 单个或批量软删除数据.
     * @deprecated
     */
    public function destroy(string $ids): int
    {
        return $this->model::destroy(explode(',', $ids));
    }

    /**
     * 更新一条数据.
     * @deprecated
     */
    public function update($id, $data): int
    {
        $this->filterExecuteAttributes($data, true);

        return $this->model::query()->where($this->model->getKeyName(), $id)->update($data);
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
     * 单个或批量真实删除数据.
     */
    public function realDelete(string $ids)
    {
        $deleted = 0;
        foreach (array_filter(explode(',', $ids)) as $id) {
            // TODO 这里效果没验证, 盲改的
            $deleted += (int) $this->model::query()->where($this->model->getKeyName(), $id)->withTrashed()?->forceDelete();
        }

        return $deleted;

    }

    /**
     * 单个或批量从回收站恢复数据.
     */
    public function recovery(string $ids): bool
    {
        return ! empty($ids) && $this->model::query()->withTrashed()->whereIn($this->model->getKeyName(), explode(',', $ids))->restore();
    }

    /**
     * 单个或批量禁用数据.
     */
    public function disable($ids, string $field = 'status'): bool
    {
        return !empty($ids) && $this->model::query()
                ->whereIn($this->model->getKeyName(), explode(',', (string) $ids))
                ->update([$field => $this->model::DISABLE]);
    }

    /**
     * 单个或批量启用数据.
     */
    public function enable($ids, string $field = 'status'): bool
    {
        return !empty($ids) && $this->model::query()
            ->whereIn($this->model->getKeyName(), explode(',', (string) $ids))
            ->update([$field => $this->model::ENABLE]);

    }

    /**
     * 修改数据状态
     */
    public function changeStatus($id, $value, $field = 'status') : bool
    {
        return (string) $value === AbstractModel::ENABLE ? $this->enable($id, $field) : $this->disable($id, $field);
    }

    /**
     * 数字运算操作
     * @param int $id
     * @param string $field
     * @param string $type
     * @param int $value
     * @return bool
     */
    public function numberOperation(int $id, string $field, string $type = 'inc', int $value = 1): int
    {
        if ($type === 'inc') {
            return $this->model::query()->find($id, [ $field ])->increment($field, $value);
        }
        if ($type === 'dec') {
            return $this->model::query()->find($id, [ $field ])->decrement($field, $value);
        }

        return 0;
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

        return (new Collection())->export($dto, $filename, $this->getList($params, extend: $extend)->toArray());
    }

    /**
     * 数据导入
     * @param string $dto
     * @param \Closure|null $closure
     * @return bool
     * @Transaction
     */
    public function import(string $dto, ?\Closure $closure = null): bool
    {
        return (new Collection())->import($dto, $this->model, $closure);
    }

    /**
     * 数组数据转分页数据显示.
     */
    public function getArrayToPageList(?array $params = [], string $pageName = 'page'): array
    {
        $collect = $this->handleArraySearch(collect($this->getArrayData($params)), $params);

        $page = (int) ($params[$pageName] ?? container()->get(RequestInterface::class)->input($pageName, 1));
        $pageSize = (int) ($params['pageSize'] ?? container()->get(RequestInterface::class)->input('pageSize', $this->model?->getPerPage() ?? 15)); // 传参 > 提交 > model 默认 > 15
        $data = $collect->forPage($page, $pageSize)->toArray();
        sort($data);

        return [
            'items' => $data,
            'pageInfo' => [
                'total' => $collect->count(),
                'currentPage' => $page,
                'totalPage' => ceil($collect->count() / $pageSize),
            ],
        ];
    }

    /**
     * 数组数据搜索器.
     * @return ModelCollection
     */
    protected function handleArraySearch(\Hyperf\Utils\Collection $collect, array $params): \Hyperf\Utils\Collection
    {
        return $collect;
    }

    /**
     * 设置需要分页的数组数据.
     */
    protected function getArrayData(array $params = []): array
    {
        return [];
    }

    /**
     * 过滤新增或写入不存在的字段
     * @param array $data
     * @param bool $removePk
     */
    public function filterExecuteAttributes(array &$data, bool $removePk = false): void
    {
        $attrs = $this->model->getFillable();
        foreach ($data as $name => $val) {
            if (!in_array($name, $attrs)) {
                unset($data[$name]);
            }
        }

        if ($removePk && isset($data[$this->model->getKeyName()])) {
            unset($data[$this->model->getKeyName()]);
        }
    }
}
