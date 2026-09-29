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

namespace Bitrix24\SDK\Services\Catalog\Measure\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Services\Catalog\Measure\Result\MeasureAddedBatchResult;
use Bitrix24\SDK\Services\Catalog\Measure\Result\MeasureItemResult;
use Bitrix24\SDK\Services\Catalog\Measure\Result\MeasureUpdatedBatchResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['catalog']))]
class Batch
{
    public function __construct(protected BatchOperationsInterface $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch list of measurement units
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/measure/catalog-measure-list.html
     *
     * @param array<string, 'asc'|'desc'|'ASC'|'DESC'>           $order
     * @param array<string, scalar|array{0?: scalar, 1?:scalar}> $filter
     * @param array<int, string>                                 $select
     *
     * @return Generator<int, MeasureItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'catalog.measure.list',
        'https://apidocs.bitrix24.com/api-reference/catalog/measure/catalog-measure-list.html',
        'Batch list of measurement units'
    )]
    public function list(array $order = [], array $filter = [], array $select = [], ?int $limit = null): Generator
    {
        $this->log->debug(
            'batchList',
            [
                'order' => $order,
                'filter' => $filter,
                'select' => $select,
                'limit' => $limit,
            ]
        );

        foreach (
            $this->batch->getTraversableListWithCount(
                'catalog.measure.list',
                $order,
                $filter,
                $select,
                $limit
            ) as $key => $value
        ) {
            yield $key => new MeasureItemResult($value);
        }
    }

    /**
     * Batch adding measurement units
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/measure/catalog-measure-add.html
     *
     * @param array<int, array{
     *   code: int,
     *   measureTitle: string,
     *   isDefault?: string,
     *   symbol?: string,
     *   symbolIntl?: string,
     *   symbolLetterIntl?: string
     * }> $measures
     *
     * @return Generator<int, MeasureAddedBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'catalog.measure.add',
        'https://apidocs.bitrix24.com/api-reference/catalog/measure/catalog-measure-add.html',
        'Batch adding measurement units'
    )]
    public function add(array $measures): Generator
    {
        $items = [];
        foreach ($measures as $measure) {
            $items[] = ['fields' => $measure];
        }

        foreach ($this->batch->addEntityItems('catalog.measure.add', $items) as $key => $item) {
            yield $key => new MeasureAddedBatchResult($item);
        }
    }

    /**
     * Batch update measurement units
     *
     * Update elements in array with structure:
     * id => [
     *   'fields' => [] // fields to update
     * ]
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/measure/catalog-measure-update.html
     *
     * @param array<int, array{fields: array}> $entityItems
     *
     * @return Generator<int, MeasureUpdatedBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'catalog.measure.update',
        'https://apidocs.bitrix24.com/api-reference/catalog/measure/catalog-measure-update.html',
        'Batch update measurement units'
    )]
    public function update(array $entityItems): Generator
    {
        foreach ($this->batch->updateEntityItems('catalog.measure.update', $entityItems) as $key => $item) {
            yield $key => new MeasureUpdatedBatchResult($item);
        }
    }

    /**
     * Batch delete measurement units
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/measure/catalog-measure-delete.html
     *
     * @param int[] $measureIds
     *
     * @return Generator<int, DeletedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'catalog.measure.delete',
        'https://apidocs.bitrix24.com/api-reference/catalog/measure/catalog-measure-delete.html',
        'Batch delete measurement units'
    )]
    public function delete(array $measureIds): Generator
    {
        foreach ($this->batch->deleteEntityItems('catalog.measure.delete', $measureIds) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }
}
