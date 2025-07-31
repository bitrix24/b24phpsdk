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

namespace Bitrix24\SDK\Services\Entity\Item\Property\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
//use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Result\AddedItemBatchResult;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Services\Entity\Item\Property\Result\PropertyItemResult;
use Bitrix24\SDK\Services\Entity\Item\Property\Batch as PropertyBatch;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['entity']))]
readonly class Batch
{
    public function __construct(
        protected PropertyBatch $batch,
        protected LoggerInterface $log
    ) {
    }

    #[ApiBatchMethodMetadata(
        'entity.item.property.get',
        'https://apidocs.bitrix24.com/api-reference/entity/items/properties/entity-item-property-get.html',
        'Retrieve a list of additional properties of storage elements in batch mode'
    )]
    public function get(string $entity, array $propertyCodes): Generator
    {
        foreach (
            $this->batch->getPropertyList(
                'entity.item.property.get',
                $entity,
                $propertyCodes
            ) as $key => $value
        ) {
            yield $key => new PropertyItemResult($value->getResult());
        }
    }

    #[ApiBatchMethodMetadata(
        'entity.item.property.add',
        'https://apidocs.bitrix24.com/api-reference/entity/items/properties/entity-item-property-add.html',
        'Add in batch mode a list of additional properties'
    )]
    public function add(string $entity, array $properties): Generator
    {
        $items = [];
        foreach ($properties as $item) {
            $items[] = array_merge(
                [
                    'ENTITY' => $entity
                ],
                $item
            );
        }

        foreach ($this->batch->addEntityItems('entity.item.property.add', $items) as $key => $item) {
            yield $key => new AddedItemBatchResult($item);
        }
    }

    #[ApiBatchMethodMetadata(
        'entity.item.property.delete',
        'https://apidocs.bitrix24.com/api-reference/entity/sections/entity-section-delete.html',
        'Delete in batch mode a list of storage sections'
    )]
    public function delete(string $entity, array $propertyCodes): Generator
    {
        foreach (
            $this->batch->deleteEntityItems(
                'entity.item.property.delete',
                $propertyCodes,
                ['ENTITY' => $entity]
            ) as $key => $item
        ) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }

    #[ApiBatchMethodMetadata(
        'entity.item.property.update',
        'https://apidocs.bitrix24.com/api-reference/entity/items/properties/entity-item-property-update.html',
        'Update in batch mode a list of additional properties'
    )]
    public function update(string $entity, array $items): Generator
    {
        $dataForUpdate = [];
        foreach ($items as $item) {
            unset($item['ENTITY']);
            $dataForUpdate[] = array_merge(
                [
                    'ENTITY' => $entity
                ],
                $item
            );
        }

        foreach ($this->batch->updateEntityItems('entity.item.property.update', $dataForUpdate) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }
}
