<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Sale\Delivery\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * Class DeliveryItemResult
 *
 * @property-read int|null $ID
 * @property-read int|null $PARENT_ID
 * @property-read string|null $NAME
 * @property-read string|null $ACTIVE
 * @property-read string|null $DESCRIPTION
 * @property-read string|null $CURRENCY
 * @property-read int|null $SORT
 * @property-read int|null $LOGOTYPE
 */
class DeliveryItemResult extends AbstractItem
{
}