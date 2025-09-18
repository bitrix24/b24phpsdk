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

use Bitrix24\SDK\Core\Result\AbstractItem;
use Carbon\CarbonImmutable;

/**
 * @property-read int|null $id
 * @property-read int|null $orderDeliveryId
 * @property-read int|null $basketId
 * @property-read string|null $quantity
 * @property-read string|null $reservedQuantity
 * @property-read CarbonImmutable $dateInsert
 * @property-read string|null $xmlId
 */
class ShipmentItemItemResult extends AbstractItem
{
}
