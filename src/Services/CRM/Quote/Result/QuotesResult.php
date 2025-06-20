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

namespace Bitrix24\SDK\Services\CRM\Quote\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class QuotesResult
 *
 * @package Bitrix24\SDK\Services\CRM\Quote\Result
 */
class QuotesResult extends AbstractResult
{
    /**
     * @return QuoteItemResult[]
     * @throws BaseException
     */
    public function getQuotes(): array
    {
        $items = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult() as $item) {
            $items[] = new QuoteItemResult($item);
        }

        return $items;
    }
}
