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

namespace Bitrix24\SDK\Services\Catalog\Product\Sku\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Services\Catalog\Product\Sku\Result\SkuAddedBatchResult;
use Bitrix24\SDK\Services\Catalog\Product\Sku\Result\SkuItemResult;
use Bitrix24\SDK\Services\Catalog\Product\Sku\Result\SkuUpdatedBatchResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['catalog']))]
class Batch
{
    public function __construct(protected BatchOperationsInterface $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch list of parent (SKU) products
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/product/sku/catalog-product-sku-list.html
     *
     * @param array<string, 'asc'|'desc'|'ASC'|'DESC'>           $order
     * @param array<string, scalar|array{0?: scalar, 1?:scalar}> $filter
     * @param array<int, string>                                 $select
     *
     * @return Generator<int, SkuItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'catalog.product.sku.list',
        'https://apidocs.bitrix24.com/api-reference/catalog/product/sku/catalog-product-sku-list.html',
        'Batch list of parent (SKU) products'
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
                'catalog.product.sku.list',
                $order,
                $filter,
                $select,
                $limit
            ) as $key => $value
        ) {
            yield $key => new SkuItemResult($value);
        }
    }

    /**
     * Batch adding parent (SKU) products
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/product/sku/catalog-product-sku-add.html
     *
     * @param array<int, array> $skus
     *
     * @return Generator<int, SkuAddedBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'catalog.product.sku.add',
        'https://apidocs.bitrix24.com/api-reference/catalog/product/sku/catalog-product-sku-add.html',
        'Batch adding parent (SKU) products'
    )]
    public function add(array $skus): Generator
    {
        $items = [];
        foreach ($skus as $sku) {
            $items[] = ['fields' => $sku];
        }

        foreach ($this->batch->addEntityItems('catalog.product.sku.add', $items) as $key => $item) {
            yield $key => new SkuAddedBatchResult($item);
        }
    }

    /**
     * Batch update parent (SKU) products
     *
     * Update elements in array with structure:
     * id => [
     *   'fields' => [] // fields to update
     * ]
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/product/sku/catalog-product-sku-update.html
     *
     * @param array<int, array{fields: array}> $entityItems
     *
     * @return Generator<int, SkuUpdatedBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'catalog.product.sku.update',
        'https://apidocs.bitrix24.com/api-reference/catalog/product/sku/catalog-product-sku-update.html',
        'Batch update parent (SKU) products'
    )]
    public function update(array $entityItems): Generator
    {
        foreach ($this->batch->updateEntityItems('catalog.product.sku.update', $entityItems) as $key => $item) {
            yield $key => new SkuUpdatedBatchResult($item);
        }
    }

    /**
     * Batch delete parent (SKU) products
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/product/sku/catalog-product-sku-delete.html
     *
     * @param int[] $skuIds
     *
     * @return Generator<int, DeletedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'catalog.product.sku.delete',
        'https://apidocs.bitrix24.com/api-reference/catalog/product/sku/catalog-product-sku-delete.html',
        'Batch delete parent (SKU) products'
    )]
    public function delete(array $skuIds): Generator
    {
        foreach ($this->batch->deleteEntityItems('catalog.product.sku.delete', $skuIds) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }
}
