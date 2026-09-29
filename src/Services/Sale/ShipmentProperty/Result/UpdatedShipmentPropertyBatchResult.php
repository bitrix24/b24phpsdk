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

use Bitrix24\SDK\Core\Result\UpdatedItemBatchResult;

/**
 * Class UpdatedShipmentPropertyBatchResult
 *
 * Result of the sale.shipmentproperty.update command executed in batch mode
 *
 * @package Bitrix24\SDK\Services\Sale\ShipmentProperty\Result
 */
class UpdatedShipmentPropertyBatchResult extends UpdatedItemBatchResult
{
    #[\Override]
    public function isSuccess(): bool
    {
        return isset($this->getResponseData()->getResult()['property']);
    }

    public function property(): ShipmentPropertyItemResult
    {
        return new ShipmentPropertyItemResult($this->getResponseData()->getResult()['property']);
    }
}
