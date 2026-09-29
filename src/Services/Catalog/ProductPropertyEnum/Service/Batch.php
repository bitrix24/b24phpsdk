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

namespace Bitrix24\SDK\Services\Catalog\ProductPropertyEnum\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Services\Catalog\ProductPropertyEnum\Result\ProductPropertyEnumAddedBatchResult;
use Bitrix24\SDK\Services\Catalog\ProductPropertyEnum\Result\ProductPropertyEnumItemResult;
use Bitrix24\SDK\Services\Catalog\ProductPropertyEnum\Result\ProductPropertyEnumUpdatedBatchResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['catalog']))]
class Batch
{
    public function __construct(protected BatchOperationsInterface $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch list of list-type property values
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/product-property-enum/catalog-product-property-enum-list.html
     *
     * @param array<string, 'asc'|'desc'|'ASC'|'DESC'>           $order
     * @param array<string, scalar|array{0?: scalar, 1?:scalar}> $filter
     * @param array<int, string>                                 $select
     *
     * @return Generator<int, ProductPropertyEnumItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'catalog.productPropertyEnum.list',
        'https://apidocs.bitrix24.com/api-reference/catalog/product-property-enum/catalog-product-property-enum-list.html',
        'Batch list of list-type property values'
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
                'catalog.productPropertyEnum.list',
                $order,
                $filter,
                $select,
                $limit
            ) as $key => $value
        ) {
            yield $key => new ProductPropertyEnumItemResult($value);
        }
    }

    /**
     * Batch adding list-type property values
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/product-property-enum/catalog-product-property-enum-add.html
     *
     * @param array<int, array{
     *   propertyId: int,
     *   value: string,
     *   xmlId: string,
     *   def?: string,
     *   sort?: int
     * }> $productPropertyEnums
     *
     * @return Generator<int, ProductPropertyEnumAddedBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'catalog.productPropertyEnum.add',
        'https://apidocs.bitrix24.com/api-reference/catalog/product-property-enum/catalog-product-property-enum-add.html',
        'Batch adding list-type property values'
    )]
    public function add(array $productPropertyEnums): Generator
    {
        $items = [];
        foreach ($productPropertyEnums as $productPropertyEnum) {
            $items[] = ['fields' => $productPropertyEnum];
        }

        foreach ($this->batch->addEntityItems('catalog.productPropertyEnum.add', $items) as $key => $item) {
            yield $key => new ProductPropertyEnumAddedBatchResult($item);
        }
    }

    /**
     * Batch update list-type property values
     *
     * Update elements in array with structure:
     * id => [
     *   'fields' => [] // fields to update
     * ]
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/product-property-enum/catalog-product-property-enum-update.html
     *
     * @param array<int, array{fields: array}> $entityItems
     *
     * @return Generator<int, ProductPropertyEnumUpdatedBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'catalog.productPropertyEnum.update',
        'https://apidocs.bitrix24.com/api-reference/catalog/product-property-enum/catalog-product-property-enum-update.html',
        'Batch update list-type property values'
    )]
    public function update(array $entityItems): Generator
    {
        foreach ($this->batch->updateEntityItems('catalog.productPropertyEnum.update', $entityItems) as $key => $item) {
            yield $key => new ProductPropertyEnumUpdatedBatchResult($item);
        }
    }

    /**
     * Batch delete list-type property values
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/product-property-enum/catalog-product-property-enum-delete.html
     *
     * @param int[] $productPropertyEnumIds
     *
     * @return Generator<int, DeletedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'catalog.productPropertyEnum.delete',
        'https://apidocs.bitrix24.com/api-reference/catalog/product-property-enum/catalog-product-property-enum-delete.html',
        'Batch delete list-type property values'
    )]
    public function delete(array $productPropertyEnumIds): Generator
    {
        foreach (
            $this->batch->deleteEntityItems(
                'catalog.productPropertyEnum.delete',
                $productPropertyEnumIds
            ) as $key => $item
        ) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }
}
