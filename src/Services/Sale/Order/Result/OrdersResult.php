<?php

namespace Bitrix24\SDK\Services\Sale\Order\Result;

use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class OrdersResult
 * Represents the result of multiple orders operation.
 */
class OrdersResult extends AbstractResult
{
    /**
     * Returns array of OrderItemResult
     * @return OrderItemResult[]
     */
    public function getOrders(): array
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();
        $orders = $result['orders'] ?? [];
        return array_map(fn($order) => new OrderItemResult($order), $orders);
    }

}
