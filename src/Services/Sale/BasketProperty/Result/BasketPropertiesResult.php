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

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * List of basket properties result for sale.basketproperties.list
 */
class BasketPropertiesResult extends AbstractResult
{
    /**
     * @return BasketPropertyItemResult[]
     * @throws BaseException
     */
    public function getBasketProperties(): array
    {
        $items = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult()['basketProperties'] as $item) {
            $items[] = new BasketPropertyItemResult($item);
        }

        return $items;
    }

}
