<?php

declare(strict_types=1);

namespace Ece2\Common\Constants;

use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\Annotation\Constants;

#[Constants]
class ErrorCode extends AbstractConstants
{
    /**
     * @Message("Server Error！")
     */
    public const SERVER_ERROR = 500;

    /**
     * @Message("TOKEN 过期或者不存在")
     */
    public const TOKEN_EXPIRED = 1001;

    /**
     * @Message("数据验证失败")
     */
    public const VALIDATE_FAILED = 1002;

    /**
     * @Message("没有权限")
     */
    public const NO_PERMISSION = 1003;

    /**
     * @Message("没有数据")
     */
    public const NO_DATA = 1004;

    /**
     * @Message("正常状态异常代码")
     */
    public const NORMAL_STATUS = 1005;
}
