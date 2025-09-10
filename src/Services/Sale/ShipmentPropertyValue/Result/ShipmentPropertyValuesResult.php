<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Sale\ShipmentPropertyValue\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * List result for sale.shipmentpropertyvalue.list
 */
class ShipmentPropertyValuesResult extends AbstractResult
{
    /**
     * @return ShipmentPropertyValueItemResult[]
     * @throws BaseException
     */
    public function getPropertyValues(): array
    {
        $items = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult()['propertyValues'] as $item) {
            $items[] = new ShipmentPropertyValueItemResult($item);
        }

        return $items;
    }

    /**
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     */
    public function getTotal(): int
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();
        return isset($result['total'])
            ? (int)$result['total']
            : (int)($this->getCoreResponse()->getResponseData()->getPagination()->getTotal() ?? 0);
    }
}
