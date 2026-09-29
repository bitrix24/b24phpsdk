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

namespace Bitrix24\SDK\Services\Sale\PropertyRelation\Result;

use Bitrix24\SDK\Core\Result\AddedItemBatchResult;

/**
 * Class PropertyRelationAddedBatchResult
 *
 * Result of the sale.propertyRelation.add command executed in batch mode.
 * Property relation has no own identifier, getId() returns the bound entity identifier.
 *
 * @package Bitrix24\SDK\Services\Sale\PropertyRelation\Result
 */
class PropertyRelationAddedBatchResult extends AddedItemBatchResult
{
    #[\Override]
    public function getId(): int
    {
        return (int)$this->getResponseData()->getResult()['propertyRelation']['entityId'];
    }

    public function propertyRelation(): PropertyRelationItemResult
    {
        return new PropertyRelationItemResult($this->getResponseData()->getResult()['propertyRelation']);
    }
}
