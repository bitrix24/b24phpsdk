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

namespace Bitrix24\SDK\Services\Sale\Payment\Result;

use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class PaymentsResult
 * Represents the result of a list payments operation.
 */
class PaymentsResult extends AbstractResult
{
    /**
     * Returns an array of payment items
     *
     * @return PaymentItemResult[] array of payment item results
     */
    public function getPayments(): array
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();
        $payments = $result['payments'] ?? [];
        return array_map(fn ($payment): PaymentItemResult => new PaymentItemResult($payment), $payments);
    }
}
