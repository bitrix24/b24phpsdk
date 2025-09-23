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
use Bitrix24\SDK\Core\Result\UpdatedItemResult;

/**
 * Class PaymentItemShipmentUpdatedResult
 * Represents the result of an update payment item shipment binding operation.
 */
class PaymentItemShipmentUpdatedResult extends UpdatedItemResult
{
    /**
     * @throws BaseException
     */
    public function isSuccess(): bool
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();
        return isset($result['paymentItemShipment']) && !empty($result['paymentItemShipment']);
    }
}
