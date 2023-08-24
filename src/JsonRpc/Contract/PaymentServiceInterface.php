<?php

namespace Ece2\Common\JsonRpc\Contract;

interface PaymentServiceInterface
{
    /**
     * 支付单.
     * @param string $tradeNo 支付台单号
     * @param array $orderInfo 订单信息
     * @return array
     */
    public function order(string $tradeNo, array $orderInfo): array;

    /**
     * 退款.
     * @param string $tradeNo 支付台单号
     * @param int $refundAmount 退款金额
     * @return array
     */
    public function refund(string $tradeNo, int $refundAmount): array;

    public function getByWhereRaw($sql, $bindings = [], $boolean = 'and');
}
