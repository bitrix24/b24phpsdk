<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Vadim Soluyanov <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */


declare(strict_types=1);

namespace Bitrix24\SDK\Services\CRM\Deal\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class DealRecurringExposeResult
 *
 * @package Bitrix24\SDK\Services\CRM\Deal\Result
 */
class DealRecurringExposeResult extends AbstractResult
{
    /**
     * @return int
     * @throws BaseException
     */
    public function getDealId(): int
    {
        return intval($this->getCoreResponse()->getResponseData()->getResult()['DEAL_ID']);
    }
}