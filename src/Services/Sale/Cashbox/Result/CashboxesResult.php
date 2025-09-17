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

namespace Bitrix24\SDK\Services\Sale\Cashbox\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Result for sale.cashbox.list
 */
class CashboxesResult extends AbstractResult
{
    /**
     * @return CashboxItemResult[]
     * @throws BaseException
     */
    public function getCashboxes(): array
    {
        $result = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult() as $item) {
            $result[] = new CashboxItemResult($item);
        }

        return $result;
    }
}
