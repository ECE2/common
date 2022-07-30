<?php

namespace Ece2\Common\JsonRpc\Contract;

interface SystemDeptServiceInterface
{
    public function getByIds(array $ids);

    /**
     * 根据组集集合模糊查询获取部门数据.
     * @param $deptId
     * @return mixed
     */
    public function getByLevelFuzzy($deptId);

    /**
     * 部门下的所有人 ID (包含部门下的部门).
     * @param $id
     * @return mixed
     */
    public function everyoneIds($id);

    /**
     * 部门的顶级部门.
     * @param $deptId
     * @return mixed
     */
    public function topLevelDept($deptId);
}
