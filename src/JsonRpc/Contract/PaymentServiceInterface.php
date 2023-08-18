<?php

namespace Ece2\Common\JsonRpc\Contract;

interface PaymentServiceInterface
{
    /**
     * 支付单.
     * @param string $tradeNo 支付台单号
     * @param array $orderInfo 订单信息
     */
    public function order(string $tradeNo, array $orderInfo);
}
