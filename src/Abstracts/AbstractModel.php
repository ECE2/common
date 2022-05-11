<?php

declare(strict_types=1);

namespace Ece2\Common\Abstracts;

use Ece2\Common\Model\Traits\DataPermission;
use Ece2\Common\Model\Traits\HasRelationshipsForRpc;
use Hyperf\DbConnection\Model\Model as BaseModel;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\ModelCache\Cacheable;

abstract class AbstractModel extends BaseModel
{
    use HasRelationshipsForRpc;
    use Cacheable;
    use DataPermission;

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
}
