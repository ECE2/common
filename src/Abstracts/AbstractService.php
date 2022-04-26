<?php

declare(strict_types=1);

namespace Ece2\Common\Abstracts;

use Ece2\Common\Annotation\Transaction;
use Ece2\Common\Collection;
use Ece2\Common\Response;
use Ece2\Common\Traits\ServiceTrait;
use Hyperf\Context\Context;
use Hyperf\Database\Model\Collection as ModelCollection;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Utils\ApplicationContext;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractService
{
    /**
     * @var AbstractMapper
     */
    public $mapper;

    /**
     * 把数据设置为类属性
     * @param array $data
     */
    public function setAttributes(array $data)
    {
        Context::set('attributes', $data);
    }

    /**
     * 魔术方法，从类属性里获取数据
     * @param string $name
     * @return mixed|string
     */
    public function __get(string $name)
    {
        return $this->getAttributes()[$name] ?? '';
    }

    /**
     * 获取数据
     * @return array
     */
    public function getAttributes(): array
    {
        return Context::get('attributes', []);
    }

    /**
     * 获取列表数据.
     */
    public function getList(?array $params = null, bool $isScope = true): array
    {
        if ($params['select'] ?? null) {
            $params['select'] = explode(',', $params['select']);
        }
        $params['recycle'] = false;
        return $this->mapper->getList($params, $isScope);
    }

    /**
     * 从回收站过去列表数据.
     */
    public function getListByRecycle(?array $params = null, bool $isScope = true): array
    {
        if ($params['select'] ?? null) {
            $params['select'] = explode(',', $params['select']);
        }
        $params['recycle'] = true;
        return $this->mapper->getList($params, $isScope);
    }

    /**
     * 获取列表数据（带分页）.
     */
    public function getPageList(?array $params = null, bool $isScope = true): array
    {
        if ($params['select'] ?? null) {
            $params['select'] = explode(',', $params['select']);
        }
        return $this->mapper->getPageList($params, $isScope);
    }

    /**
     * 从回收站获取列表数据（带分页）.
     */
    public function getPageListByRecycle(?array $params = null, bool $isScope = true): array
    {
        if ($params['select'] ?? null) {
            $params['select'] = explode(',', $params['select']);
        }
        $params['recycle'] = true;
        return $this->mapper->getPageList($params, $isScope);
    }

    /**
     * 获取树列表.
     */
    public function getTreeList(?array $params = null, bool $isScope = true): array
    {
        if ($params['select'] ?? null) {
            $params['select'] = explode(',', $params['select']);
        }
        $params['recycle'] = false;
        return $this->mapper->getTreeList($params, $isScope);
    }

    /**
     * 从回收站获取树列表.
     */
    public function getTreeListByRecycle(?array $params = null, bool $isScope = true): array
    {
        if ($params['select'] ?? null) {
            $params['select'] = explode(',', $params['select']);
        }
        $params['recycle'] = true;
        return $this->mapper->getTreeList($params, $isScope);
    }

    /**
     * 新增数据.
     */
    public function save(array $data): int
    {
        return $this->mapper->save($data);
    }

    /**
     * 批量新增.
     */
    #[Transaction]
    public function batchSave(array $collects): bool
    {
        foreach ($collects as $collect) {
            $this->mapper->save($collect);
        }
        return true;
    }

    /**
     * 读取一条数据.
     */
    public function read(int $id): ?AbstractModel
    {
        return $this->mapper->read($id);
    }

    /**
     * Description:获取单个值
     * User:mike.
     * @return null|\Hyperf\Utils\HigherOrderTapProxy|mixed|void
     */
    public function value(array $condition, string $columns = 'id')
    {
        return $this->mapper->value($condition, $columns);
    }

    /**
     * Description:获取单列值
     * User:mike.
     * @return null|array
     */
    public function pluck(array $condition, string $columns = 'id'): array
    {
        return $this->mapper->pluck($condition, $columns);
    }

    /**
     * 从回收站读取一条数据.
     * @noinspection PhpUnused
     */
    public function readByRecycle(int $id): AbstractModel
    {
        return $this->mapper->readByRecycle($id);
    }

    /**
     * 单个或批量软删除数据.
     */
    public function delete(string $ids): bool
    {
        return ! empty($ids) && $this->mapper->delete(explode(',', $ids));
    }

    /**
     * 更新一条数据.
     */
    public function update(int $id, array $data): bool
    {
        return $this->mapper->update($id, $data);
    }

    /**
     * 按条件更新数据.
     */
    public function updateByCondition(array $condition, array $data): bool
    {
        return $this->mapper->updateByCondition($condition, $data);
    }

    /**
     * 单个或批量真实删除数据.
     */
    public function realDelete(string $ids): bool
    {
        return ! empty($ids) && $this->mapper->realDelete(explode(',', $ids));
    }

    /**
     * 单个或批量从回收站恢复数据.
     */
    public function recovery(string $ids): bool
    {
        return ! empty($ids) && $this->mapper->recovery(explode(',', $ids));
    }

    /**
     * 单个或批量禁用数据.
     */
    public function disable(string $ids, string $field = 'status'): bool
    {
        return ! empty($ids) && $this->mapper->disable(explode(',', $ids), $field);
    }

    /**
     * 单个或批量启用数据.
     */
    public function enable(string $ids, string $field = 'status'): bool
    {
        return ! empty($ids) && $this->mapper->enable(explode(',', $ids), $field);
    }

    /**
     * 修改数据状态
     */
    public function changeStatus(int $id, string $value): bool
    {
        return $value === AbstractModel::ENABLE ? $this->mapper->enable([$id], $filed) : $this->mapper->disable([$id], $filed);
    }

    /**
     * 数字运算操作
     * @param int $id
     * @param string $field
     * @param string $type
     * @param int $value
     * @return bool
     */
    public function numberOperation(int $id, string $field, string $type = 'inc', int $value = 1): bool
    {
        if ($type === 'inc') {
            return $this->mapper->inc($id, $field, $value);
        }
        if ($type === 'dec') {
            return $this->mapper->dec($id, $field, $value);
        }

        return false;
    }

    /**
     * 导出数据.
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function export(array $params, ?string $dto, string $filename = null): ResponseInterface
    {
        if (empty($dto)) {
            return ApplicationContext::getContainer()->get(Response::class)->error('导出未指定DTO');
        }

        if (empty($filename)) {
            $filename = $this->mapper->getModel()->getTable();
        }

        return (new Collection())->export($dto, $filename, $this->mapper->getList($params));
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
        return $this->mapper->import($dto, $closure);
    }

    /**
     * 数组数据转分页数据显示.
     */
    public function getArrayToPageList(?array $params = [], string $pageName = 'page'): array
    {
        $collect = $this->handleArraySearch(collect($this->getArrayData($params)), $params);

        $pageSize = $this->mapper?->model?->getPerPage() ?? container()->get(RequestInterface::class)->input('pageSize', 15);
        $page = 1;

        if ($params[$pageName] ?? false) {
            $page = (int) $params[$pageName];
        }

        if ($params['pageSize'] ?? false) {
            $pageSize = (int) $params['pageSize'];
        }

        $data = $collect->forPage($page, $pageSize)->toArray();

        return [
            'items' => $this->getCurrentArrayPageBefore($data, $params),
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
     * 数组当前页数据返回之前处理器，默认对key重置.
     */
    protected function getCurrentArrayPageBefore(array &$data, array $params = []): array
    {
        sort($data);
        return $data;
    }

    /**
     * 设置需要分页的数组数据.
     */
    protected function getArrayData(array $params = []): array
    {
        return [];
    }
}
