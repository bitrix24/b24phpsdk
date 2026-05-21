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

namespace Bitrix24\SDK\Services\Biconnector\Source\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Services\Biconnector\Source\Result\AddedSourceBatchResult;
use Bitrix24\SDK\Services\Biconnector\Source\Result\DeletedSourceBatchResult;
use Bitrix24\SDK\Services\Biconnector\Source\Result\SourceItemResult;
use Bitrix24\SDK\Services\Biconnector\Source\Result\UpdatedSourceBatchResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['biconnector']))]
class Batch
{
    /**
     * Batch constructor
     */
    public function __construct(protected BatchOperationsInterface $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch list sources
     *
     * @link https://apidocs.bitrix24.com/api-reference/biconnector/source/biconnector-source-list.html
     *
     * @return Generator<int, SourceItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'biconnector.source.list',
        'https://apidocs.bitrix24.com/api-reference/biconnector/source/biconnector-source-list.html',
        'Batch list sources'
    )]
    public function list(
        array $order = [],
        array $filter = [],
        array $select = [],
        ?int $limit = null
    ): Generator {
        $this->log->debug(
            'batchList',
            [
                'order'  => $order,
                'filter' => $filter,
                'select' => $select,
                'limit'  => $limit,
            ]
        );

        foreach (
            $this->batch->getTraversableList(
                'biconnector.source.list',
                $order,
                $filter,
                $select,
                $limit
            ) as $key => $value
        ) {
            yield $key => new SourceItemResult($value);
        }
    }

    /**
     * Batch add sources
     *
     * @link https://apidocs.bitrix24.com/api-reference/biconnector/source/biconnector-source-add.html
     *
     * @param array<int, array{
     *     title: string,
     *     connectorId: int,
     *     description?: string,
     *     active?: bool,
     *     settings?: array,
     * }> $sources
     *
     * @return Generator<int, AddedSourceBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'biconnector.source.add',
        'https://apidocs.bitrix24.com/api-reference/biconnector/source/biconnector-source-add.html',
        'Batch add sources'
    )]
    public function add(array $sources): Generator
    {
        $items = [];
        foreach ($sources as $item) {
            $items[] = [
                'fields' => $item,
            ];
        }

        foreach ($this->batch->addEntityItems('biconnector.source.add', $items) as $key => $item) {
            yield $key => new AddedSourceBatchResult($item);
        }
    }

    /**
     * Batch update sources
     *
     * Update elements in array with structure:
     * id => [  // Source id
     *     'fields' => [] // Source fields to update
     * ]
     *
     * @param array<int, array> $entityItems
     *
     * @return Generator<int, UpdatedSourceBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'biconnector.source.update',
        'https://apidocs.bitrix24.com/api-reference/biconnector/source/biconnector-source-update.html',
        'Batch update sources'
    )]
    public function update(array $entityItems): Generator
    {
        foreach (
            $this->batch->updateEntityItems(
                'biconnector.source.update',
                $entityItems
            ) as $key => $item
        ) {
            yield $key => new UpdatedSourceBatchResult($item);
        }
    }

    /**
     * Batch delete sources
     *
     * @param int[] $sourceIds
     *
     * @return Generator<int, DeletedSourceBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'biconnector.source.delete',
        'https://apidocs.bitrix24.com/api-reference/biconnector/source/biconnector-source-delete.html',
        'Batch delete sources'
    )]
    public function delete(array $sourceIds): Generator
    {
        foreach (
            $this->batch->deleteEntityItems(
                'biconnector.source.delete',
                $sourceIds
            ) as $key => $item
        ) {
            yield $key => new DeletedSourceBatchResult($item);
        }
    }
}
