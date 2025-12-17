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
use Bitrix24\SDK\Core\Result\FieldsResult;

/**
 * Class PaymentItemShipmentFieldsResult
 * Represents the result of a payment item shipment binding fields operation.
 */
class PaymentItemShipmentFieldsResult extends FieldsResult
{
    /**
     * @throws BaseException
     */
    #[\Override]
    public function getFieldsDescription(): array
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();
        return $result['paymentItemShipment'] ?? [];
    }
}
