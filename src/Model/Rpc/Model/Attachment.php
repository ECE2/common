<?php

declare(strict_types=1);

namespace Ece2\Common\Model\Rpc\Model;

use Ece2\Common\JsonRpc\Contract\AttachmentServiceInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Utils\ApplicationContext;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class Attachment extends Base
{
    /**
     * @Inject
     * @var AttachmentServiceInterface
     */
    private $attachmentService;

    /**
     * 用于 rpc 连表查询.
     * @param $params
     * @param mixed $ids
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @return array
     */
    public static function get($ids = [])
    {
        $attachmentService = ApplicationContext::getContainer()->get(AttachmentServiceInterface::class);

        return array_map(static fn ($model) => new static($model), $attachmentService->getByIds($ids)['data'] ?? []);
    }
}
