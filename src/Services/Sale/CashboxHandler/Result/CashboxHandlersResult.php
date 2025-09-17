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

namespace Bitrix24\SDK\Services\Sale\CashboxHandler\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Result for sale.cashbox.handler.list
 */
class CashboxHandlersResult extends AbstractResult
{
    /**
     * @return CashboxHandlerItemResult[]
     * @throws BaseException
     */
    public function getCashboxHandlers(): array
    {
        $result = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult() as $item) {
            $result[] = new CashboxHandlerItemResult($item);
        }

        return $result;
    }
}