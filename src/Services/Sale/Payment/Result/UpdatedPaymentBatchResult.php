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

use Bitrix24\SDK\Core\Result\UpdatedItemBatchResult;
use Bitrix24\SDK\Core\Exceptions\BaseException;

/**
 * Class UpdatedPaymentBatchResult
 * Represents a result of batch update payments operation
 *
 * @package Bitrix24\SDK\Services\Sale\Payment\Result
 */
class UpdatedPaymentBatchResult extends UpdatedItemBatchResult
{
    /**
     * @throws BaseException
     */
    public function isSuccess(): bool
    {
        return (bool)$this->getResponseData()->getResult()['payment'];
    }
}
