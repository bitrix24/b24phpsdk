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

namespace Bitrix24\SDK\Services\Sale\Order\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * Class OrderItemResult
 * Represents a single order item (order) returned by Bitrix24 REST API.
 *
 * @property-read int $ID
 * @property-read int $USER_ID
 * @property-read string $STATUS_ID
 * @property-read float $PRICE
 * @property-read string $CURRENCY
 * @property-read array $BASKET
 * @property-read string $DATE_INSERT
 * @property-read string $DATE_UPDATE
 * @property-read string $PAYED
 * @property-read string $DEDUCTED
 * @property-read string $CANCELED
 * @property-read string $MARKED
 * @property-read string $REASON_MARKED
 * @property-read string $COMMENTS
 * @property-read string $RESPONSIBLE_ID
 * @property-read string $ACCOUNT_NUMBER
 * @property-read array $FIELDS
 * // Add other order fields as needed
 */
class OrderItemResult extends AbstractItem
{
}
