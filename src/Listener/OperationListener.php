<?php

declare(strict_types=1);

namespace Ece2\Common\Listener;

use App\Service\SystemOperLogService;
use Ece2\Common\Event\Operation;
use Ece2\Common\JsonRpc\Contract\SystemQueueLogServiceInterface;
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

    public function process(object $event)
    {
        /** @var Operation $event */
        $requestInfo = $event->getRequestInfo();
        $requestInfo['request_data'] = Json::encode($requestInfo['request_data']);

        $service = container()->get(is_base_system() ? SystemOperLogService::class : SystemQueueLogServiceInterface::class);
        $service->save($requestInfo);
    }
}
