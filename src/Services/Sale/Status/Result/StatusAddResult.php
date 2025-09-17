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

namespace Bitrix24\SDK\Services\Sale\Status\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AddedItemResult;

/**
 * Class StatusAddResult - result of adding a status
 *
 * @package Bitrix24\SDK\Services\Sale\Status\Result
 */
class StatusAddResult extends AddedItemResult
{
    /**
     * Get status object
     *
     * @throws BaseException
     */
    public function getStatus(): StatusItemResult
    {
        return new StatusItemResult($this->getCoreResponse()->getResponseData()->getResult()['status']);
    }

    /**
     * Check if the request was successful
     */
    public function isSuccess(): bool
    {
        try {
            // If we can get the result data without exceptions, the request was successful
            return $this->getCoreResponse()->getResponseData()->getResult() !== null;
        } catch (BaseException) {
            return false;
        }
    }
}
