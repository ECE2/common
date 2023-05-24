<?php

declare(strict_types=1);

namespace Ece2\Common\Abstracts;

use Ece2\Common\Model\Traits\DataPermission;
use Ece2\Common\Model\Traits\HasRelationshipsForRpc;
use Hyperf\DbConnection\Model\Model as BaseModel;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\ModelCache\Cacheable;
use Hyperf\ModelCache\CacheableInterface;

abstract class AbstractModel extends BaseModel implements CacheableInterface
{
    use HasRelationshipsForRpc;
    use Cacheable;
    use DataPermission;

    /**
     * 状态: 可用.
     */
    public const ENABLE = 1;

    /**
     * 状态: 不可用.
     */
    public const DISABLE = 2;

    /**
     * 允许前端提交的最大单页数据数量.
     */
    public const MAX_PAGE_SIZE = 999;

    /**
     * 隐藏的字段列表.
     * @var string[]
     */
    protected array $hidden = ['deleted_at'];

    /**
     * 允许前端传长度.
     * @return int
     */
    public function getPerPage(): int
    {
        try {
            /** @var RequestInterface $request */
            $request = container()->get(RequestInterface::class);

            return min((int) $request->input('pageSize', parent::getPerPage()), static::MAX_PAGE_SIZE);
        } catch (\Throwable $e) {
            return parent::getPerPage();
        }
    }
}
