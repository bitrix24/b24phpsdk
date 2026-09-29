<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <algonexys@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Sale\PaymentItemBasket\Result;

use Bitrix24\SDK\Core\Result\AddedItemBatchResult;

/**
 * Class PaymentItemBasketAddedBatchResult
 *
 * Result of the sale.paymentitembasket.add command executed in batch mode
 *
 * @package Bitrix24\SDK\Services\Sale\PaymentItemBasket\Result
 */
class PaymentItemBasketAddedBatchResult extends AddedItemBatchResult
{
    #[\Override]
    public function getId(): int
    {
        return (int)$this->getResponseData()->getResult()['paymentItemBasket']['id'];
    }

    public function paymentItemBasket(): PaymentItemBasketItemResult
    {
        return new PaymentItemBasketItemResult($this->getResponseData()->getResult()['paymentItemBasket']);
    }
}
