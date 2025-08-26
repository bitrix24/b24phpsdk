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

namespace Bitrix24\SDK\Services\Sale\PropertyVariant\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * List of property variants result for sale.propertyvariant.list
 */
class PropertyVariantsResult extends AbstractResult
{
    /**
     * @return PropertyVariantItemResult[]
     * @throws BaseException
     */
    public function getPropertyVariants(): array
    {
        $items = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult()['propertyVariants'] as $item) {
            $items[] = new PropertyVariantItemResult($item);
        }

        return $items;
    }

    /**
     * @throws BaseException
     */
    public function getTotal(): int
    {
        return (int)$this->getCoreResponse()->getResponseData()->getPagination()->getTotal();
    }
}
