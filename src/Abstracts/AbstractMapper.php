<?php

declare (strict_types = 1);

namespace Ece2\Common\Abstracts;

use App\Model\SystemUploadfile;
use Ece2\Common\Annotation\Transaction;
use Ece2\Common\Collection;
use Hyperf\Context\Context;
use Hyperf\Contract\LengthAwarePaginatorInterface;
use Hyperf\Database\Model\Builder;
use Hyperf\Paginator\Paginator;

/**
 * @property AbstractModel $model
 */
abstract class AbstractMapper
{
    public $model;

    public function __get(string $name)
    {
        return Context::get(static::class . ':attributes', [])[$name] ?? '';
    }

    /**
     * 把数据设置为类属性
     * @param array $data
     */
    public function setAttributes(array $data)
    {
        Context::set(static::class . ':attributes', $data);
    }

    /**
     * 获取数据
     * @return array
     */
    public function getAttributes(): array
    {
        return Context::get(static::class . ':attributes', []);
    }

    /**
     * 返回模型查询构造器
     * @param array|null $params
     * @param bool $isScope
     * @return Builder
     */
    public function listQuerySetting(?array $params, bool $isScope): Builder
    {
        $query = $this->model::query()
            ->when($params['recycle'] ?? false, fn ($query) => $query->onlyTrashed())
            ->when($params['select'] ?? false, fn ($query) => $query->select($this->filterQueryAttributes($params['select'])))
            ->when($isScope, fn ($query) => $query->userDataScope());

        $this->handleOrder($query, $params);
        return $this->handleSearch($query, $params);
    }

    /**
     * 获取列表数据
     * @param array|null $params
     * @param bool $isScope
     * @return array
     */
    public function getList(?array $params, bool $isScope = true): array
    {
        return $this->listQuerySetting($params, $isScope)
            ->get()
            ->toArray();
    }

    /**
     * 获取列表数据（带分页）
     * @param array|null $params
     * @param bool $isScope
     * @param string $pageName
     * @return array
     */
    public function getPageList(?array $params, bool $isScope = true, string $pageName = 'page'): array
    {
        return $this->setPaginate(
            $this
                ->listQuerySetting($params, $isScope)
                ->paginate(
                    perPage: (int) ($params['pageSize'] ?? $this->model::getModel()->getPerPage()),
                    pageName: $pageName,
                    page: (int) ($params[$pageName] ?? Paginator::resolveCurrentPage($pageName))
                )
        );
    }

    /**
     * 设置数据库分页
     * @param LengthAwarePaginatorInterface $paginate
     * @return array
     */
    public function setPaginate(LengthAwarePaginatorInterface $paginate): array
    {
        return [
            'items' => $paginate->items(),
            'pageInfo' => [
                'total' => $paginate->total(),
                'currentPage' => $paginate->currentPage(),
                'totalPage' => $paginate->lastPage()
            ]
        ];
    }

    /**
     * 获取树列表
     * @param array|null $params
     * @param bool $isScope
     * @param string $id
     * @param string $parentField
     * @param string $children
     * @return array
     */
    public function getTreeList(
        ?array $params = null,
        bool   $isScope = true,
        string $id = 'id',
        string $parentField = 'parent_id',
        string $children = 'children'
    ): array
    {
        $params['_mainAdmin_tree'] = true;
        $params['_mainAdmin_tree_pid'] = $parentField;
        $data = $this->listQuerySetting($params, $isScope)->get();

        return $data->toTree(null, $data[0]->{$parentField} ?? 0, $id, $parentField, $children);
    }

    /**
     * 排序处理器
     * @param Builder $query
     * @param array|null $params
     * @return Builder
     */
    public function handleOrder(Builder $query, ?array $params = null): Builder
    {
        return $query
            // 对树型数据强行加个排序
            ->when(isset($params['_mainAdmin_tree']), fn($query) => $query->orderBy($params['_mainAdmin_tree_pid']))
            ->when($params['orderBy'] ?? false, function ($query) use ($params) {
                if (is_array($params['orderBy'])) {
                    foreach ($params['orderBy'] as $key => $order) {
                        $query->orderBy($order, $params['orderType'][$key] ?? 'asc');
                    }
                } else {
                    $query->orderBy($params['orderBy'], $params['orderType'] ?? 'asc');
                }
            });
    }

    /**
     * 搜索处理器
     * @param Builder $query
     * @param array $params
     * @return Builder
     */
    public function handleSearch(Builder $query, array $params): Builder
    {
        return $query;
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
     * 过滤新增或写入不存在的字段
     * @param array $data
     * @param bool $removePk
     */
    protected function filterExecuteAttributes(array &$data, bool $removePk = false): void
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

    /**
     * 新增数据
     * @param array $data
     * @return int
     */
    public function save(array $data): int
    {
        $this->filterExecuteAttributes($data, $this->model->incrementing);
        $model = $this->model::create($data);
        return $model->{$model->getKeyName()};
    }

    /**
     * 读取一条数据
     * @param int $id
     * @param array $columns
     * @return Builder|Builder[]|\Hyperf\Database\Model\Collection|\Hyperf\Database\Model\Model|null
     */
    public function read(int $id, array $columns = ['*'])
    {
        return $this->model::query()->find($id, $columns);
    }

    /**
     * 按条件读取一行数据
     * @param array $condition
     * @param array $column
     * @return mixed
     */
    public function first(array $condition, array $column = ['*']): ?AbstractModel
    {
        return $this->model::query()->where($condition)->first($column);
    }

    /**
     * 获取单个值
     * @param array $condition
     * @param string $columns
     * @return \Hyperf\Utils\HigherOrderTapProxy|mixed|void|null
     */
    public function value(array $condition, string $columns = 'id')
    {
        return $this->model::query()->where($condition)->value($columns);
    }

    /**
     * 获取单列值
     * @param array $condition
     * @param string $columns
     * @return array
     */
    public function pluck(array $condition, string $columns = 'id'): array
    {
        return $this->model::query()->where($condition)->pluck($columns)->toArray();
    }

    /**
     * 从回收站读取一条数据
     * @param int $id
     * @return AbstractModel|null
     * @noinspection PhpUnused
     */
    public function readByRecycle(int $id): ?AbstractModel
    {
        return $this->model::query()->withTrashed()->find($id);
    }

    /**
     * 单个或批量软删除数据
     * @param array $ids
     * @return bool
     */
    public function delete(array $ids): bool
    {
        $this->model::destroy($ids);

        return true;
    }

    /**
     * 更新一条数据
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $this->filterExecuteAttributes($data, true);

        return $this->model::query()->find($id)?->update($data) > 0;
    }

    /**
     * 按条件更新数据
     * @param array $condition
     * @param array $data
     * @return bool
     */
    public function updateByCondition(array $condition, array $data): bool
    {
        $this->filterExecuteAttributes($data, true);
        return $this->model::query()->where($condition)->update($data) > 0;
    }

    /**
     * 单个或批量真实删除数据
     * @param array $ids
     * @return bool
     */
    public function realDelete(array $ids): bool
    {
        foreach ($ids as $id) {
            $model = $this->model::query()->withTrashed()->find($id);
            $model && $model->forceDelete();
        }
        return true;
    }

    /**
     * 单个或批量从回收站恢复数据
     * @param array $ids
     * @return bool
     */
    public function recovery(array $ids): bool
    {
        $this->model::query()->withTrashed()->whereIn($this->model->getKeyName(), $ids)->restore();
        return true;
    }

    /**
     * 单个或批量禁用数据
     * @param array $ids
     * @param string $field
     * @return bool
     */
    public function disable(array $ids, string $field = 'status'): bool
    {
        $this->model::query()->whereIn($this->model->getKeyName(), $ids)->update([$field => $this->model::DISABLE]);
        return true;
    }

    /**
     * 单个或批量启用数据
     * @param array $ids
     * @param string $field
     * @return bool
     */
    public function enable(array $ids, string $field = 'status'): bool
    {
        $this->model::query()
            ->whereIn($this->model->getKeyName(), $ids)
            ->update([$field => $this->model::ENABLE]);

        return true;
    }

    /**
     * 数据导入
     * @param string $dto
     * @param \Closure|null $closure
     * @return bool
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    #[Transaction]
    public function import(string $dto, ?\Closure $closure = null): bool
    {
        return (new Collection())->import($dto, $this->model, $closure);
    }

    /**
     * 闭包通用查询设置
     * @param \Closure|null $closure 传入的闭包查询
     * @return Builder
     */
    public function settingClosure(?\Closure $closure = null): Builder
    {
        return $this->model::query()->where(function ($query) use ($closure) {
            if ($closure instanceof \Closure) {
                $closure($query);
            }
        });
    }

    /**
     * 闭包通用方式查询一条数据
     * @param \Closure|null $closure
     * @param array|string[] $column
     * @return Builder|AbstractModel|null
     */
    public function one(?\Closure $closure = null, array $column = ['*'])
    {
        return $this->settingClosure($closure)->select($column)->first();
    }

    /**
     * 闭包通用方式查询数据集合
     * @param \Closure|null $closure
     * @param array|string[] $column
     * @return array
     */
    public function get(?\Closure $closure = null, array $column = ['*']): array
    {
        return $this->settingClosure($closure)->get($column)->toArray();
    }

    /**
     * 闭包通用方式统计
     * @param \Closure|null $closure
     * @param string $column
     * @return int
     */
    public function count(?\Closure $closure = null, string $column = '*'): int
    {
        return $this->settingClosure($closure)->count($column);
    }

    /**
     * 闭包通用方式查询最大值
     * @param \Closure|null $closure
     * @param string $column
     * @return mixed|string|void
     */
    public function max(?\Closure $closure = null, string $column = '*')
    {
        return $this->settingClosure($closure)->max($column);
    }

    /**
     * 闭包通用方式查询最小值
     * @param \Closure|null $closure
     * @param string $column
     * @return mixed|string|void
     */
    public function min(?\Closure $closure = null, string $column = '*')
    {
        return $this->settingClosure($closure)->min($column);
    }

    /**
     * 自增
     * @param int $id
     * @param string $field
     * @param int $value
     * @return bool
     */
    public function inc(int $id, string $field, int $value): bool
    {
        return $this->model::query()->find($id, [ $field ])->increment($field, $value) > 0;
    }

    /**
     * 自减
     * @param int $id
     * @param string $field
     * @param int $value
     * @return bool
     */
    public function dec(int $id, string $field, int $value): bool
    {
        return $this->model::query()->find($id, [ $field ])->decrement($field, $value) > 0;
    }
}
