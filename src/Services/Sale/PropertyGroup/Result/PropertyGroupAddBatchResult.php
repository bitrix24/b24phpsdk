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

namespace Bitrix24\SDK\Services\Sale\PropertyGroup\Result;

use Bitrix24\SDK\Core\Result\AddedItemBatchResult;

/**
 * Class PropertyGroupAddBatchResult
 *
 * Result of the sale.propertygroup.add command executed in batch mode
 *
 * @package Bitrix24\SDK\Services\Sale\PropertyGroup\Result
 */
class PropertyGroupAddBatchResult extends AddedItemBatchResult
{
    #[\Override]
    public function getId(): int
    {
        return (int)$this->getResponseData()->getResult()['propertyGroup']['id'];
    }

    public function propertyGroup(): PropertyGroupItemResult
    {
        return new PropertyGroupItemResult($this->getResponseData()->getResult()['propertyGroup']);
    }
}
