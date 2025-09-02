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
use Carbon\CarbonImmutable;
use Money\Money;

/**
 * Class OrderItemResult
 * Represents a single order item (order) returned by Bitrix24 REST API.
 *
 * Fields and their types are taken from Bitrix24 API (sale.order.getfields).
 *
 * @property-read string|null $accountNumber
 * @property-read string|null $additionalInfo
 * @property-read int|null    $affiliateId
 * @property-read bool|null $canceled
 * @property-read string|null $comments
 * @property-read int|null    $companyId
 * @property-read string|null $currency
 * @property-read CarbonImmutable|null $dateCanceled
 * @property-read CarbonImmutable|null $dateInsert
 * @property-read CarbonImmutable|null $dateLock
 * @property-read CarbonImmutable|null $dateMarked
 * @property-read CarbonImmutable|null $dateStatus
 * @property-read CarbonImmutable|null $dateUpdate
 * @property-read bool|null $deducted
 * @property-read Money|null  $discountValue
 * @property-read int|null    $empCanceledId
 * @property-read int|null    $empMarkedId
 * @property-read int|null    $empStatusId
 * @property-read bool|null $externalOrder
 * @property-read int|null    $id
 * @property-read string|null $id1c
 * @property-read string|null $lid
 * @property-read bool|null $lockedBy
 * @property-read bool|null $marked
 * @property-read string|null $orderTopic
 * @property-read bool|null $payed
 * @property-read int|null    $personTypeId
 * @property-read string|null $personTypeXmlId
 * @property-read Money|null  $price
 * @property-read string|null $reasonCanceled
 * @property-read string|null $reasonMarked
 * @property-read bool|null $recountFlag
 * @property-read bool|null $recurringId
 * @property-read int|null    $responsibleId
 * @property-read bool|null $statusId
 * @property-read string|null $statusXmlId
 * @property-read Money|null  $taxValue
 * @property-read bool|null $updated1c
 * @property-read string|null $userDescription
 * @property-read int|null    $userId
 * @property-read int|null    $version
 * @property-read string|null $version1c
 * @property-read string|null $xmlId
 */
class OrderItemResult extends AbstractItem
{
}
