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
 * Class BasketItemResult
 * Represents the result of retrieving basket item information
 *
 * @package Bitrix24\SDK\Services\Sale\BasketItem\Result
 */
class BasketItemResult extends AbstractResult
{
    /**
     * Get basket item information
     */
    public function basketItem(): BasketItemItemResult
    {
        return new BasketItemItemResult($this->getCoreResponse()->getResponseData()->getResult()['basketItem']);
    }
}
