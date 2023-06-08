<?php

declare(strict_types=1);

namespace Ece2\Common\Model\Rpc\Model;

use Ece2\Common\JsonRpc\Contract\AttachmentServiceInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use function Hyperf\Support\make;

class Attachment extends Base
{
    protected static function getService()
    {
        return make(AttachmentServiceInterface::class);
    }
}
