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

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class PaymentItemShipmentsResult
 * Represents the result of a list payment item shipment bindings operation.
 */
class PaymentItemShipmentsResult extends AbstractResult
{
    /**
     * Returns an array of payment item shipment binding items
     *
     * @return PaymentItemShipmentItemResult[] array of payment item shipment binding item results
     * @throws BaseException
     */
    public function getPaymentItemShipments(): array
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();
        $paymentItemShipments = $result['paymentItemsShipment'] ?? [];
        return array_map(fn ($item): PaymentItemShipmentItemResult => new PaymentItemShipmentItemResult($item), $paymentItemShipments);
    }
}
