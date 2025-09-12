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

namespace Bitrix24\SDK\Services\Sale\Shipment\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class ShipmentsResult
 *
 * @package Bitrix24\SDK\Services\Sale\Shipment\Result
 */
class ShipmentsResult extends AbstractResult
{
    /**
     * @return ShipmentItemResult[]
     * @throws BaseException
     */
    public function getShipments(): array
    {
        $items = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult()['shipments'] as $item) {
            $items[] = new ShipmentItemResult($item);
        }

        return $items;
    }

}
