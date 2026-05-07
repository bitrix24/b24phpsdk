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

use Bitrix24\SDK\Core\Result\UpdatedItemResult;

/**
 * Class UpdatedBasketItemResult represents the result from updating a basket item in an order
 *
 * @package Bitrix24\SDK\Services\Sale\BasketItem\Result
 */
class UpdatedBasketItemResult extends UpdatedItemResult
{
    /**
     * @return bool true if update operation was successful
     */
    #[\Override]
    public function isSuccess(): bool
    {
        return isset($this->getCoreResponse()->getResponseData()->getResult()['basketItem']);
    }
}
