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

namespace Bitrix24\SDK\Services\Paysystem\Handler\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * List of payment system handlers result for sale.paysystem.handler.list
 */
class HandlersResult extends AbstractResult
{
    /**
     * @return HandlerItemResult[]
     * @throws BaseException
     */
    public function getHandlers(): array
    {
        $items = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult() as $item) {
            $items[] = new HandlerItemResult($item);
        }

        return $items;
    }
}