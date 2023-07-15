<?php

namespace Ece2\Common\Abstracts;

use Ece2\Common\Traits\JsonRpcTrait;

abstract class AbstractJsonRpcService
{
    /**
     * @return array
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function success(mixed $data = [], ?string $message = null, int $code = 200)
    {
        return [
            'success' => true,
            'code' => $code,
            'message' => $message ?: t('response_success'),
            'data' => $data,
        ];
    }

    /**
     * @return array
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function error(string $message = '', int $code = 500, array $data = [])
    {
        return [
            'success' => false,
            'code' => $code,
            'message' => $message ?: t('response_error'),
            'data' => $data,
        ];
    }

    /**
     * 通过ID获取数据.
     * @param array $ids
     * @return array
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getByIds(array $ids)
    {
        return $this->success($this->model::query()->find($ids)->toArray());
    }

    /**
     * 通过查询条件获取数据.
     * @param string $sql
     * @param array $bindings
     * @param string $boolean
     * @return array
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getByWhereRaw($sql, $bindings = [], $boolean = 'and')
    {
        return $this->success($this->model::query()->whereRaw($sql, $bindings, $boolean)->get()->toArray());
    }

    /**
     * 更新一条数据.
     * @param int $id
     * @param array $data
     * @return array
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function update($id, $data)
    {
        return $this->success($this->model::query()->findOrFail($id)?->update($data));
    }
}
