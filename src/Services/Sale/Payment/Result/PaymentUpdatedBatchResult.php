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

namespace Bitrix24\SDK\Services\Sale\Payment\Result;

use Bitrix24\SDK\Core\Result\UpdatedItemBatchResult;

/**
 * Class PaymentUpdatedBatchResult
 *
 * Result of the sale.payment.update command executed in batch mode
 *
 * @package Bitrix24\SDK\Services\Sale\Payment\Result
 */
class PaymentUpdatedBatchResult extends UpdatedItemBatchResult
{
    #[\Override]
    public function isSuccess(): bool
    {
        return isset($this->getResponseData()->getResult()['payment']);
    }

    public function payment(): PaymentItemResult
    {
        return new PaymentItemResult($this->getResponseData()->getResult()['payment']);
    }
}
