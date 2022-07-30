<?php

declare(strict_types=1);

namespace Ece2\Common\Model\Rpc\Model;

use Ece2\Common\JsonRpc\Contract\SystemDeptServiceInterface;
use Hyperf\Cache\Annotation\Cacheable;
use Hyperf\Di\Annotation\Inject;

/**
 * @property int $id 主键
 * @property int $parent_id 父ID
 * @property string $level 组级集合
 * @property string $name 部门名称
 * @property string $leader 负责人
 * @property string $phone 联系电话
 * @property string $status 状态 (0正常 1停用)
 * @property int $sort 排序
 * @property array $console_component 控制器组件
 * @property int $created_by 创建者
 * @property int $updated_by 更新者
 * @property \Carbon\Carbon $created_at 创建时间
 * @property \Carbon\Carbon $updated_at 更新时间
 * @property string $deleted_at 删除时间
 * @property string $remark 备注
 */
class SystemDept extends Base
{
    /**
     * @Inject
     * @var SystemDeptServiceInterface
     */
    protected static $service;

    /**
     * 部门下的所有人 ID (包含部门下的部门).
     * @return array
     */
    public function everyoneIds()
    {
        return self::$service->everyoneIds($this->getKey())['data'] ?? [];
    }

    /**
     * 部门的顶级部门.
     * @return SystemDept
     */
    public function topLevelDept()
    {
        return new SystemDept(self::$service->topLevelDept($this->getKey())['data'] ?? []);
    }
}
