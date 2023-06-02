<?php

declare(strict_types=1);

namespace Ece2\Common\Model\Rpc\Model;

use Ece2\Common\JsonRpc\Contract\AttachmentServiceInterface;
use Hyperf\Di\Annotation\Inject;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

use function Hyperf\Support\make;

class Attachment extends Base
{
    protected static function getService()
    {
        return make(AttachmentServiceInterface::class);
    }

    /**
     * 用于 rpc 连表查询.
     * @param $ids
     * @return array|Attachment[]
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public static function get($ids = [])
    {
        $attachmentService = container()->get(AttachmentServiceInterface::class);

        return array_map(static fn ($model) => new static($model), $attachmentService->getByIds($ids)['data'] ?? []);
    }
}
