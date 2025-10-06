<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Sale\DeliveryHandler\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * Class DeliveryHandlerItemResult
 *
 * @property-read int $ID
 * @property-read string $NAME
 * @property-read string $CODE
 * @property-read int $SORT
 * @property-read string $DESCRIPTION
 * @property-read array $SETTINGS
 * @property-read array $PROFILES
 */
class DeliveryHandlerItemResult extends AbstractItem
{
}
