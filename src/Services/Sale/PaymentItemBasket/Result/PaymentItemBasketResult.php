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

namespace Bitrix24\SDK\Services\Sale\PaymentItemBasket\Result;

use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class PaymentItemBasketResult
 * Represents the result of a single payment item basket binding operation.
 */
class PaymentItemBasketResult extends AbstractResult
{
    /**
     * Returns the payment item basket binding as PaymentItemBasketItemResult
     */
    public function paymentItemBasket(): PaymentItemBasketItemResult
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();
        return new PaymentItemBasketItemResult($result['paymentItemBasket'] ?? []);
    }
}