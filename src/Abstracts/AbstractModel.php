<?php

declare(strict_types=1);

namespace Ece2\Common\Abstracts;

use Ece2\Common\Model\Traits\DataPermission;
use Ece2\Common\Model\Traits\HasRelationshipsForRpc;
use Hyperf\Database\Model\ModelIDE;
use Hyperf\DbConnection\Model\Model as BaseModel;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\ModelCache\Cacheable;
use Hyperf\ModelCache\CacheableInterface;

/**
 * @mixin ModelIDE
 */
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
     * 不能大规模分配的属性.
     * @var array
     */
    protected array $guarded = [];

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

    /**
     * 覆盖 HasAttribute 的 json 编码.
     */
    protected function asJson(mixed $value): string|false
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }
}
