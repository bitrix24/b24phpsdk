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
 * Class PaymentResult
 * Represents the result of a single payment operation.
 */
class PaymentResult extends AbstractResult
{
    /**
     * Returns the payment as PaymentItemResult
     */
    public function payment(): PaymentItemResult
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();
        return new PaymentItemResult($result['payment'] ?? []);
    }
}
