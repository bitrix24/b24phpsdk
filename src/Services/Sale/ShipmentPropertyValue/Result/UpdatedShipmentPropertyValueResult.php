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

namespace Bitrix24\SDK\Services\Sale\ShipmentPropertyValue\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\UpdatedItemResult;

/**
 * Updated result for sale.shipmentpropertyvalue.modify
 */
class UpdatedShipmentPropertyValueResult extends UpdatedItemResult
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
     * @throws BaseException
     */
    public function isSuccess(): bool
    {
        return isset($this->getCoreResponse()->getResponseData()->getResult()['propertyValues']);
    }
}
