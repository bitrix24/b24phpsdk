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

namespace Bitrix24\SDK\Services\Sale\BasketProperty\Result;

use Bitrix24\SDK\Core\Contracts\AddedItemIdResultInterface;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Result for sale.basketproperties.add
 */
class BasketPropertyAddResult extends AbstractResult implements AddedItemIdResultInterface
{
    /**
     * @throws BaseException
     */
    public function getId(): int
    {
        return (int)$this->getCoreResponse()->getResponseData()->getResult()['basketProperty']['id'];
    }

    /**
     * @throws BaseException
     */
    public function getBasketProperty(): BasketPropertyItemResult
    {
        return new BasketPropertyItemResult($this->getCoreResponse()->getResponseData()->getResult()['basketProperty']);
    }
}
