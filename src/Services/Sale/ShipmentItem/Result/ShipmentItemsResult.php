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

namespace Bitrix24\SDK\Services\Sale\ShipmentItem\Result;

use Bitrix24\SDK\Core\Result\AbstractResult;

class ShipmentItemsResult extends AbstractResult
{
    /**
     * @return ShipmentItemItemResult[]
     */
    public function getShipmentItems(): array
    {
        $items = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult()['shipmentItems'] as $item) {
            $items[] = new ShipmentItemItemResult($item);
        }

        return $items;
    }

}
