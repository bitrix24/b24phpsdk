<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Entity\Item\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AddedItemBatchResult;
use Bitrix24\SDK\Core\Result\AddedItemResult;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Core\Result\UpdatedItemBatchResult;
use Bitrix24\SDK\Services\CRM\Deal\Result\DealItemResult;
use Generator;
use Psr\Log\LoggerInterface;

readonly class Batch
{
    public function __construct(
        protected BatchOperationsInterface $batch,
        protected LoggerInterface $log
    ) {
    }

    #[ApiBatchMethodMetadata(
        'entity.item.add',
        'https://apidocs.bitrix24.com/api-reference/entity/items/entity-item-add.html',
        'Add in batch mode a list of storage elements'
    )]
    public function add(string $entity, array $items): Generator
    {
        $elements = [];
        foreach ($items as $item) {
            $elements[] = array_merge(
                [
                    'ENTITY' => $entity
                ],
                $item
            );
        }
        foreach ($this->batch->addEntityItems('entity.item.add', $elements) as $key => $item) {
            yield $key => new AddedItemBatchResult($item);
        }
    }

    #[ApiBatchMethodMetadata(
        'entity.item.delete',
        'https://apidocs.bitrix24.com/api-reference/entity/items/entity-item-delete.html',
        'Delete in batch mode a list of storage elements'
    )]
    public function delete(string $entity, array $itemIds): Generator
    {
        foreach (
            $this->batch->deleteEntityItems(
                'entity.item.delete',
                $itemIds,
                ['ENTITY' => $entity]
            ) as $key => $item
        ) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }
}