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

use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class QuoteResult
 *
 * @package Bitrix24\SDK\Services\CRM\Quote\Result
 */
class QuoteResult extends AbstractResult
{
    /**
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     */
    public function quote(): QuoteItemResult
    {
        return new QuoteItemResult($this->getCoreResponse()->getResponseData()->getResult());
    }
}
