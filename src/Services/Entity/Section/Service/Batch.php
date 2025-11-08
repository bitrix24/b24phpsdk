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

namespace Bitrix24\SDK\Services\Entity\Section\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Result\AddedItemBatchResult;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Services\Entity\Section\Result\SectionItemResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['entity']))]
readonly class Batch
{
    public function __construct(
        protected BatchOperationsInterface $batch,
        protected LoggerInterface $log
    ) {
    }

    #[ApiBatchMethodMetadata(
        'entity.section.get',
        'https://apidocs.bitrix24.com/api-reference/entity/sections/entity-section-get.html',
        'Get the list of storage sections in batch mode'
    )]
    public function get(string $entity, array $sort = [], array $filter = [], ?int $limit = null): Generator
    {
        foreach (
            $this->batch->getTraversableList(
                'entity.section.get',
                $sort,
                $filter,
                [],
                $limit,
                ['ENTITY' => $entity]
            ) as $key => $value
        ) {
            yield $key => new SectionItemResult($value);
        }
    }

    #[ApiBatchMethodMetadata(
        'entity.section.add',
        'https://apidocs.bitrix24.com/api-reference/entity/sections/entity-section-add.html',
        'Add in batch mode a list of storage sections'
    )]
    public function add(string $entity, array $items): Generator
    {
        $sections = [];
        foreach ($items as $item) {
            $sections[] = array_merge(
                [
                    'ENTITY' => $entity
                ],
                $item
            );
        }

        foreach ($this->batch->addEntityItems('entity.section.add', $sections) as $key => $item) {
            yield $key => new AddedItemBatchResult($item);
        }
    }

    #[ApiBatchMethodMetadata(
        'entity.section.delete',
        'https://apidocs.bitrix24.com/api-reference/entity/sections/entity-section-delete.html',
        'Delete in batch mode a list of storage sections'
    )]
    public function delete(string $entity, array $itemIds): Generator
    {
        foreach (
            $this->batch->deleteEntityItems(
                'entity.section.delete',
                $itemIds,
                ['ENTITY' => $entity]
            ) as $key => $item
        ) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }

    #[ApiBatchMethodMetadata(
        'entity.section.update',
        'https://apidocs.bitrix24.com/api-reference/entity/sections/entity-section-update.html',
        'Update in batch mode a list of storage sections'
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

        foreach ($this->batch->updateEntityItems('entity.section.update', $dataForUpdate) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }
}
