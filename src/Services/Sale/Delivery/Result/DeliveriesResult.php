<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Sale\Delivery\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * List of deliveries result for sale.delivery.getlist
 */
class DeliveriesResult extends AbstractResult
{
    /**
     * @return DeliveryItemResult[]
     * @throws BaseException
     */
    public function getDeliveries(): array
    {
        $items = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult() as $item) {
            $items[] = new DeliveryItemResult($item);
        }

        return $items;
    }
}
