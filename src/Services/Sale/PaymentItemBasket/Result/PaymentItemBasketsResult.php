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

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class PaymentItemBasketsResult
 * Represents the result of a list payment item basket bindings operation.
 */
class PaymentItemBasketsResult extends AbstractResult
{
    /**
     * Returns an array of payment item basket binding items
     *
     * @return PaymentItemBasketItemResult[] array of payment item basket binding item results
     * @throws BaseException
     */
    public function getPaymentItemBaskets(): array
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();
        $paymentItemBaskets = $result['paymentItemsBasket'] ?? [];
        return array_map(fn ($item): PaymentItemBasketItemResult => new PaymentItemBasketItemResult($item), $paymentItemBaskets);
    }
}