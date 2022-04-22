<?php

namespace Ece2\Common\JsonRpc\Contract;

interface SystemQueueLogServiceInterface
{
    public function save(array $data);

    /**
     * 更新消费状态
     * @param $id
     * @param $consumeStatus
     * @param string $logContent
     * @return mixed
     */
    public function changeConsumeStatus($id, $consumeStatus, string $logContent = '');

    public function changeProduceStatus($id, $produceStatus, string $logContent = '');
}
