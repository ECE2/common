<?php

declare(strict_types=1);

namespace Ece2\Common;

use Ece2\Common\Abstracts\AbstractModel;
use Hyperf\Database\Model\Collection as BaseCollection;
use Ece2\Common\Office\Excel\PhpOffice;
use Ece2\Common\Office\Excel\XlsWriter;

class Collection extends BaseCollection
{
    /**
     * 系统菜单转前端路由树
     * @return array
     */
    public function sysMenuToRouterTree(): array
    {
        if ($this->isEmpty()) {
            return [];
        }

        $routers = [];
        $this->each(function ($menu) use (&$routers) {
            $routers[] = [
                'id' => $menu['id'],
                'parent_id' => $menu['parent_id'],
                'name' => $menu['code'],
                'component' => $menu['component'],
                'path' => '/' . $menu['route'],
                'redirect' => $menu['redirect'],
                'meta' => [
                    'type' => $menu['type'],
                    'icon' => $menu['icon'],
                    'title' => $menu['name'],
                    'hidden' => ($menu['is_hidden'] == 0),
                    'hiddenBreadcrumb' => false
                ]
            ];
        });

        return $this->toTree($routers);
    }

    /**
     * @param array $data
     * @param int $parentId
     * @param string $id
     * @param string $parentField
     * @param string $children
     * @return array
     */
    public function toTree(?array $data = null, int $parentId = 0, string $id = 'id', string $parentField = 'parent_id', string $children = 'children'): array
    {
        if ($data === null && empty($data = $data ?: $this->toArray())) {
            return [];
        }

        $tree = [];
        foreach ($data as $value) {
            if ((int) $value[$parentField] !== $parentId) {
                continue;
            }

            $child = $this->toTree($data, $value[$id], $id, $parentField, $children);
            if (!empty($child)) {
                $value[$children] = $child;
            }

            array_push($tree, $value);
        }

        return $tree;
    }

    /**
     * 导出数据
     * @param string $dto
     * @param string $filename
     * @param array|\Closure|null $closure
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function export(string $dto, string $filename, array|\Closure $closure = null): \Psr\Http\Message\ResponseInterface
    {
        $excelDrive = config('excel_drive');
        if ($excelDrive === 'auto') {
            $excel = extension_loaded('xlswriter') ? new XlsWriter($dto) : new PhpOffice($dto);
        } else {
            $excel = $excelDrive === 'xlsWriter' ? new XlsWriter($dto) : new PhpOffice($dto);
        }
        return $excel->export($filename, is_null($closure) ? $this->toArray() : $closure);
    }

    /**
     * 数据导入
     * @param string $dto
     * @param AbstractModel $model
     * @param \Closure|null $closure
     * @return bool
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function import(string $dto, AbstractModel $model, ?\Closure $closure = null): bool
    {
        $excelDrive = config('excel_drive');
        if ($excelDrive === 'auto') {
            $excel = extension_loaded('xlswriter') ? new XlsWriter($dto) : new PhpOffice($dto);
        } else {
            $excel = $excelDrive === 'xlsWriter' ? new XlsWriter($dto) : new PhpOffice($dto);
        }
        return $excel->import($model, $closure);
    }

}
