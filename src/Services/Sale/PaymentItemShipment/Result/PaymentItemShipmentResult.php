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

use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class PaymentItemShipmentResult
 * Represents the result of a single payment item shipment binding operation.
 */
class PaymentItemShipmentResult extends AbstractResult
{
    /**
     * Returns the payment item shipment binding as PaymentItemShipmentItemResult
     */
    public function paymentItemShipment(): PaymentItemShipmentItemResult
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();
        return new PaymentItemShipmentItemResult($result['paymentItemShipment'] ?? []);
    }
}
