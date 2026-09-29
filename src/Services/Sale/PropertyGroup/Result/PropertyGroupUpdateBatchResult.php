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

use Bitrix24\SDK\Core\Result\UpdatedItemBatchResult;

/**
 * Class PropertyGroupUpdateBatchResult
 *
 * Result of the sale.propertygroup.update command executed in batch mode
 *
 * @package Bitrix24\SDK\Services\Sale\PropertyGroup\Result
 */
class PropertyGroupUpdateBatchResult extends UpdatedItemBatchResult
{
    #[\Override]
    public function isSuccess(): bool
    {
        return isset($this->getResponseData()->getResult()['propertyGroup']);
    }

    public function propertyGroup(): PropertyGroupItemResult
    {
        return new PropertyGroupItemResult($this->getResponseData()->getResult()['propertyGroup']);
    }
}
