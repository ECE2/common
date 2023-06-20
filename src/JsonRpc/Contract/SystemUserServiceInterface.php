<?php

namespace Ece2\Common\JsonRpc\Contract;

interface SystemUserServiceInterface
{
    public function getByIds(array $ids);

    /** 获取超管列表 */
    public function getSuperAdmins();

    /** 根据用户 ID 获取用户所属部门 */
    public function getUserDept(int $uid);

    /** 根据 token 获取用户信息 */
    public function getInfoByJwtToken(string $jwtToken, string $scene = 'api');

    /** 获取用户信息 */
    public function getInfo($uid);

    /** 用户角色 */
    public function getRoles($uid);

    /** 根据部门 ID 数组获取用户数据 */
    public function getByDeptIds(array $deptIds);

    /** 发送消息 */
    public function sendMessageToUser($uid, $message, $type);
}
