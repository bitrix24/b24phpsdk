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
            : $this->getCoreResponse()->getResponseData()->getPagination()->getTotal() ?? 0;
    }
}
