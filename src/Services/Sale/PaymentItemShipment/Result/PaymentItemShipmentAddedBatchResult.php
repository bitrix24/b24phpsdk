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

namespace Bitrix24\SDK\Services\Sale\PaymentItemShipment\Result;

use Bitrix24\SDK\Core\Result\AddedItemBatchResult;

/**
 * Class PaymentItemShipmentAddedBatchResult
 *
 * Result of the sale.paymentitemshipment.add command executed in batch mode
 *
 * @package Bitrix24\SDK\Services\Sale\PaymentItemShipment\Result
 */
class PaymentItemShipmentAddedBatchResult extends AddedItemBatchResult
{
    #[\Override]
    public function getId(): int
    {
        return (int)$this->getResponseData()->getResult()['paymentItemShipment']['id'];
    }

    public function paymentItemShipment(): PaymentItemShipmentItemResult
    {
        return new PaymentItemShipmentItemResult($this->getResponseData()->getResult()['paymentItemShipment']);
    }
}
