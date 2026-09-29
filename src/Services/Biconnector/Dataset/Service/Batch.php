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

namespace Bitrix24\SDK\Services\Biconnector\Dataset\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Services\Biconnector\Dataset\Result\AddedDatasetBatchResult;
use Bitrix24\SDK\Services\Biconnector\Dataset\Result\DatasetItemResult;
use Bitrix24\SDK\Services\Biconnector\Dataset\Result\DeletedDatasetBatchResult;
use Bitrix24\SDK\Services\Biconnector\Dataset\Result\UpdatedDatasetBatchResult;
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
     * Batch list datasets
     *
     * @link https://apidocs.bitrix24.com/api-reference/biconnector/dataset/biconnector-dataset-list.html
     *
     * @return Generator<int, DatasetItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'biconnector.dataset.list',
        'https://apidocs.bitrix24.com/api-reference/biconnector/dataset/biconnector-dataset-list.html',
        'Batch list datasets'
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
                'biconnector.dataset.list',
                $order,
                $filter,
                $select,
                $limit
            ) as $key => $value
        ) {
            yield $key => new DatasetItemResult($value);
        }
    }

    /**
     * Batch add datasets
     *
     * @link https://apidocs.bitrix24.com/api-reference/biconnector/dataset/biconnector-dataset-add.html
     *
     * @param array<int, array{
     *     name: string,
     *     externalName: string,
     *     externalCode: string,
     *     sourceId: int,
     *     description?: string,
     *     fields?: array,
     * }> $datasets
     *
     * @return Generator<int, AddedDatasetBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'biconnector.dataset.add',
        'https://apidocs.bitrix24.com/api-reference/biconnector/dataset/biconnector-dataset-add.html',
        'Batch add datasets'
    )]
    public function add(array $datasets): Generator
    {
        $items = [];
        foreach ($datasets as $item) {
            $items[] = [
                'fields' => $item,
            ];
        }

        foreach ($this->batch->addEntityItems('biconnector.dataset.add', $items) as $key => $item) {
            yield $key => new AddedDatasetBatchResult($item);
        }
    }

    /**
     * Batch update datasets
     *
     * Update elements in array with structure:
     * id => [  // Dataset id
     *     'fields' => [] // Dataset fields to update
     * ]
     *
     * @param array<int, array> $entityItems
     *
     * @return Generator<int, UpdatedDatasetBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'biconnector.dataset.update',
        'https://apidocs.bitrix24.com/api-reference/biconnector/dataset/biconnector-dataset-update.html',
        'Batch update datasets'
    )]
    public function update(array $entityItems): Generator
    {
        foreach (
            $this->batch->updateEntityItems(
                'biconnector.dataset.update',
                $entityItems
            ) as $key => $item
        ) {
            yield $key => new UpdatedDatasetBatchResult($item);
        }
    }

    /**
     * Batch delete datasets
     *
     * @param int[] $datasetIds
     *
     * @return Generator<int, DeletedDatasetBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'biconnector.dataset.delete',
        'https://apidocs.bitrix24.com/api-reference/biconnector/dataset/biconnector-dataset-delete.html',
        'Batch delete datasets'
    )]
    public function delete(array $datasetIds): Generator
    {
        foreach (
            $this->batch->deleteEntityItems(
                'biconnector.dataset.delete',
                $datasetIds
            ) as $key => $item
        ) {
            yield $key => new DeletedDatasetBatchResult($item);
        }
    }
}

