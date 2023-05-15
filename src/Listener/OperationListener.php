<?php

declare(strict_types=1);

namespace Ece2\Common\Listener;

use App\Service\SystemOperationLogService;
use Ece2\Common\Event\Operation;
use Ece2\Common\JsonRpc\Contract\SystemOperationLogServiceInterface;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Utils\Codec\Json;

#[Listener]
class OperationListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            Operation::class
        ];
    }

    public function process(object $event): void
    {
        /** @var Operation $event */
        $requestInfo = $event->getRequestInfo();
        $requestInfo['request_data'] = Json::encode($requestInfo['request_data']);

        if (is_base_system()) {
            container()->get(SystemOperationLogService::class)->create($requestInfo);
        } else {
            container()->get(SystemOperationLogServiceInterface::class)->create($requestInfo);
        }
    }
}
