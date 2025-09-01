<?php

namespace Bitrix24\SDK\Services\Sale\Order\Result;

use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class OrderResult
 * Represents the result of a single order operation.
 */
class OrderResult extends AbstractResult
{
    /**
     * Returns the order as OrderItemResult
     * @return OrderItemResult
     */
    public function order(): OrderItemResult
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();
        return new OrderItemResult($result['order'] ?? []);
    }
}
