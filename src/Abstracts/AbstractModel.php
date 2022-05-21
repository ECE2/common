<?php

declare(strict_types=1);

namespace Ece2\Common\Abstracts;

use Ece2\Common\Model\Traits\DataPermission;
use Ece2\Common\Model\Traits\HasRelationshipsForRpc;
use Hyperf\DbConnection\Model\Model as BaseModel;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\ModelCache\Cacheable;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

abstract class AbstractModel extends BaseModel
{
    use HasRelationshipsForRpc;
    use Cacheable;
    use DataPermission;

    /**
     * 状态: 可用.
     */
    public const ENABLE = '0';

    /**
     * 状态: 不可用.
     */
    public const DISABLE = '1';

    public $guarded = [];

    /**
     * 隐藏的字段列表.
     * @var string[]
     */
    protected $hidden = ['deleted_at'];

    /**
     * 允许前端传长度.
     */
    public function getPerPage(): int
    {
        try {
            /** @var RequestInterface $request */
            $request = container()->get(RequestInterface::class);

            return (int) $request->input('pageSize', parent::getPerPage());
        } catch (\Throwable $e) {
            return parent::getPerPage();
        }
    }
}
