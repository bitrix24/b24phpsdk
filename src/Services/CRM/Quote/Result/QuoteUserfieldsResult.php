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

class QuoteUserfieldsResult extends AbstractResult
{
    /**
     * @return \Bitrix24\SDK\Services\CRM\Quote\Result\QuoteUserfieldItemResult[]
     * @throws BaseException
     */
    public function getUserfields(): array
    {
        $res = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult() as $item) {
            $res[] = new QuoteUserfieldItemResult($item);
        }

        return $res;
    }
}
