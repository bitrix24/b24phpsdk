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

use Bitrix24\SDK\Core\Result\AddedItemResult;

/**
 * Class AddedCatalogProductResult
 * Represents the result of adding a product from catalog to basket
 *
 * @package Bitrix24\SDK\Services\Sale\BasketItem\Result
 */
class AddedCatalogProductResult extends AddedItemResult
{
    /**
     * Get ID of the added basket item
     */
    public function getId(): int
    {
        return (int)$this->getCoreResponse()->getResponseData()->getResult()['basketItem']['id'];
    }
}
