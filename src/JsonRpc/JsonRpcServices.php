<?php

namespace Ece2\Common\JsonRpc;

use Ece2\Common\JsonRpc\Contract\AttachmentServiceInterface;
use Ece2\Common\JsonRpc\Contract\SettingConfigServiceInterface;
use Ece2\Common\JsonRpc\Contract\SettingCrontabLogServiceInterface;
use Ece2\Common\JsonRpc\Contract\SettingCrontabServiceInterface;
use Ece2\Common\JsonRpc\Contract\SystemDeptServiceInterface;
use Ece2\Common\JsonRpc\Contract\SystemMenuServiceInterface;
use Ece2\Common\JsonRpc\Contract\SystemQueueLogServiceInterface;
use Ece2\Common\JsonRpc\Contract\SystemQueueMessageServiceInterface;
use Ece2\Common\JsonRpc\Contract\SystemRoleServiceInterface;
use Ece2\Common\JsonRpc\Contract\SystemUserServiceInterface;

class JsonRpcServices
{
    public function __invoke()
    {
        return [
            'AttachmentService' => AttachmentServiceInterface::class,
            'SettingCrontabService' => SettingCrontabServiceInterface::class,
            'SettingConfigService' => SettingConfigServiceInterface::class,
            'SettingCrontabLogService' => SettingCrontabLogServiceInterface::class,
            'SystemQueueLogService' => SystemQueueLogServiceInterface::class,
            'SystemQueueMessageService' => SystemQueueMessageServiceInterface::class,
            'SystemRoleService' => SystemRoleServiceInterface::class,
            'SystemUserService' => SystemUserServiceInterface::class,
            'SystemMenuService' => SystemMenuServiceInterface::class,
            'SystemDeptService' => SystemDeptServiceInterface::class,
        ];
    }
}
