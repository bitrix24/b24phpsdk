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

namespace Bitrix24\SDK\Services\Sale\BasketItem\Result;

use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class BasketItemsResult
 * Represents a list of basket items returned by Bitrix24 REST API
 *
 * @package Bitrix24\SDK\Services\Sale\BasketItem\Result
 */
class BasketItemsResult extends AbstractResult
{
    /**
     * Get array of basket items
     *
     * @return BasketItemItemResult[]
     */
    public function getBasketItems(): array
    {
        $result = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult()['basketItems'] as $basketItem) {
            $result[] = new BasketItemItemResult($basketItem);
        }

        return $result;
    }
}
