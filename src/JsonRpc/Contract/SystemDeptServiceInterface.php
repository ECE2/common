<?php

namespace Ece2\Common\JsonRpc\Contract;

interface SystemDeptServiceInterface
{
    /**
     * 根据组集集合模糊查询获取部门数据
     * @param $deptId
     * @return mixed
     */
    public function getByLevelFuzzy($deptId);
}
