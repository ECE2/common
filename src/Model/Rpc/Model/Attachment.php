<?php

declare(strict_types=1);

namespace Ece2\Common\Model\Rpc\Model;

use Ece2\Common\JsonRpc\Contract\AttachmentServiceInterface;
use Hyperf\Di\Annotation\Inject;

class Attachment extends Base
{
    /**
     * @Inject
     * @var AttachmentServiceInterface
     */
    protected static $service;
}
