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

namespace Bitrix24\SDK\Services\Sale\PaymentItemShipment\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;
use Carbon\CarbonImmutable;

/**
 * Class PaymentItemShipmentItemResult
 * Represents a single payment item shipment binding returned by Bitrix24 REST API.
 *
 * Fields and their types are taken from Bitrix24 API (sale.paymentitemshipment.getfields).
 *
 * @property-read int|null $id Binding identifier
 * @property-read int|null $paymentId Payment identifier
 * @property-read int|null $shipmentId Shipment identifier
 * @property-read string|null $xmlId External record identifier
 * @property-read CarbonImmutable|null $dateInsert Date when the binding was created
 */
class PaymentItemShipmentItemResult extends AbstractItem
{
}
