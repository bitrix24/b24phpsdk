<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <algonexys@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Sale\ShipmentProperty\Result;

use Bitrix24\SDK\Core\Result\AddedItemBatchResult;

/**
 * Class AddedShipmentPropertyBatchResult
 *
 * Result of the sale.shipmentproperty.add command executed in batch mode
 *
 * @package Bitrix24\SDK\Services\Sale\ShipmentProperty\Result
 */
class AddedShipmentPropertyBatchResult extends AddedItemBatchResult
{
    #[\Override]
    public function getId(): int
    {
        return (int)$this->getResponseData()->getResult()['property']['id'];
    }

    public function property(): ShipmentPropertyItemResult
    {
        return new ShipmentPropertyItemResult($this->getResponseData()->getResult()['property']);
    }
}
