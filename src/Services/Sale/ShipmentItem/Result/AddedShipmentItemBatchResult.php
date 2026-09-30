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

namespace Bitrix24\SDK\Services\Sale\ShipmentItem\Result;

use Bitrix24\SDK\Core\Result\AddedItemBatchResult;

/**
 * Class AddedShipmentItemBatchResult
 *
 * Result of the sale.shipmentitem.add command executed in batch mode
 *
 * @package Bitrix24\SDK\Services\Sale\ShipmentItem\Result
 */
class AddedShipmentItemBatchResult extends AddedItemBatchResult
{
    #[\Override]
    public function getId(): int
    {
        return (int)$this->getResponseData()->getResult()['shipmentItem']['id'];
    }

    public function shipmentItem(): ShipmentItemItemResult
    {
        return new ShipmentItemItemResult($this->getResponseData()->getResult()['shipmentItem']);
    }
}
