<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Sale\DeliveryHandler\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * List of delivery handlers result for sale.delivery.handler.list
 */
class DeliveryHandlersResult extends AbstractResult
{
    /**
     * @return DeliveryHandlerItemResult[]
     * @throws BaseException
     */
    public function getDeliveryHandlers(): array
    {
        $items = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult() as $item) {
            $items[] = new DeliveryHandlerItemResult($item);
        }

        return $items;
    }
}
