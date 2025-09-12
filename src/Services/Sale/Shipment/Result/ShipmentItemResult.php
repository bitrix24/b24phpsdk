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

use Bitrix24\SDK\Core\Result\AbstractItem;
use Carbon\CarbonImmutable;

/**
 * Class ShipmentItemResult
 *
 * @property-read string $accountNumber
 * @property-read string $allowDelivery
 * @property-read float $basePriceDelivery
 * @property-read string $canceled
 * @property-read string $comments
 * @property-read int|null $companyId
 * @property-read string $currency
 * @property-read string $customPriceDelivery
 * @property-read CarbonImmutable|null $dateAllowDelivery
 * @property-read CarbonImmutable|null $dateCanceled
 * @property-read CarbonImmutable|null $dateDeducted
 * @property-read CarbonImmutable $dateInsert
 * @property-read CarbonImmutable|null $dateMarked
 * @property-read CarbonImmutable|null $dateResponsibleId
 * @property-read string $deducted
 * @property-read CarbonImmutable|null $deliveryDocDate
 * @property-read string $deliveryDocNum
 * @property-read int $deliveryId
 * @property-read string $deliveryName
 * @property-read string $deliveryXmlId
 * @property-read float $discountPrice
 * @property-read int|null $empAllowDeliveryId
 * @property-read int|null $empCanceledId
 * @property-read int|null $empDeductedId
 * @property-read int|null $empMarkedId
 * @property-read int|null $empResponsibleId
 * @property-read string $externalDelivery
 * @property-read int $id
 * @property-read string $id1c
 * @property-read string $marked
 * @property-read int $orderId
 * @property-read float $priceDelivery
 * @property-read string $reasonMarked
 * @property-read string $reasonUndoDeducted
 * @property-read int|null $responsibleId
 * @property-read array $shipmentItems
 * @property-read string $statusId
 * @property-read string $statusXmlId
 * @property-read string $system
 * @property-read string $trackingDescription
 * @property-read string $trackingLastCheck
 * @property-read string $trackingNumber
 * @property-read string $trackingStatus
 * @property-read string $updated1c
 * @property-read string $version1c
 * @property-read string $xmlId
 */
class ShipmentItemResult extends AbstractItem
{
}
