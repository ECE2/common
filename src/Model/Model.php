<?php

declare(strict_types=1);
/**
 * This file is part of api template.
 */
namespace Ece2\Common\Model;

use Ece2\Common\Collection;
use Ece2\Common\Model\Traits\HasRelationshipsForRpc;
use Ece2\Common\Traits\ModelMacroTrait;
use Hyperf\DbConnection\Model\Model as BaseModel;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\ModelCache\Cacheable;

abstract class Model extends BaseModel
{
    use HasRelationshipsForRpc;
    use Cacheable;
    use ModelMacroTrait;

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
     * 默认每页记录数
     */
    public const PAGE_SIZE = 15;

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
}
