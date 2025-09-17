<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Sale\DeliveryExtraService\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * Class DeliveryExtraServiceItemResult
 *
 * @property-read int|null $ID
 * @property-read string|null $CODE
 * @property-read string|null $NAME
 * @property-read string|null $DESCRIPTION
 * @property-read string|null $ACTIVE
 * @property-read int|null $SORT
 * @property-read string|null $TYPE
 * @property-read float|null $PRICE
 * @property-read array|null $ITEMS
 */
class DeliveryExtraServiceItemResult extends AbstractItem
{
}
