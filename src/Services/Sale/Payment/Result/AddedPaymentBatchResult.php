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

use Bitrix24\SDK\Core\Result\AddedItemBatchResult;
use Bitrix24\SDK\Core\Exceptions\BaseException;

/**
 * Class AddedPaymentBatchResult
 * Represents a result of batch add payments operation
 *
 * @package Bitrix24\SDK\Services\Sale\Payment\Result
 */
class AddedPaymentBatchResult extends AddedItemBatchResult
{
    /**
     * @throws BaseException
     */
    public function getId(): int
    {
        return (int)$this->getResponseData()->getResult()['payment']['id'];
    }
}
