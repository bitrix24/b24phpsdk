<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <algonexys@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Sale\PropertyVariant\Result;

use Bitrix24\SDK\Core\Result\AddedItemBatchResult;

/**
 * Class PropertyVariantAddBatchResult
 *
 * Result of the sale.propertyvariant.add command executed in batch mode
 *
 * @package Bitrix24\SDK\Services\Sale\PropertyVariant\Result
 */
class PropertyVariantAddBatchResult extends AddedItemBatchResult
{
    #[\Override]
    public function getId(): int
    {
        return (int)$this->getResponseData()->getResult()['propertyVariant']['id'];
    }

    public function propertyVariant(): PropertyVariantItemResult
    {
        return new PropertyVariantItemResult($this->getResponseData()->getResult()['propertyVariant']);
    }
}
