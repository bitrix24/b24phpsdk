<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Sally Fancen <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

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
        return array_map(fn ($order): \Bitrix24\SDK\Services\Sale\Order\Result\OrderItemResult => new OrderItemResult($order), $orders);
    }

}
