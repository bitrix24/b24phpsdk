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

namespace Bitrix24\SDK\Services\Catalog\ProductProperty\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Services\Catalog\ProductProperty\Result\AddedProductPropertyBatchResult;
use Bitrix24\SDK\Services\Catalog\ProductProperty\Result\DeletedProductPropertyBatchResult;
use Bitrix24\SDK\Services\Catalog\ProductProperty\Result\ProductPropertyItemResult;
use Bitrix24\SDK\Services\Catalog\ProductProperty\Result\UpdatedProductPropertyBatchResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['catalog']))]
readonly class Batch
{
    /**
     * Batch constructor
     */
    public function __construct(protected BatchOperationsInterface $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch list method for product properties
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/product-property/catalog-product-property-list.html
     *
     * @return Generator<int, ProductPropertyItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'catalog.productProperty.list',
        'https://apidocs.bitrix24.com/api-reference/catalog/product-property/catalog-product-property-list.html',
        'Batch list method for product properties'
    )]
    public function list(array $select = [], array $filter = [], array $order = [], ?int $limit = null): Generator
    {
        $itemsGenerator = $this->batch->getTraversableListWithCount(
            'catalog.productProperty.list',
            $order,
            $filter,
            $select,
            $limit
        );
        foreach ($itemsGenerator as $key => $value) {
            yield $key => new ProductPropertyItemResult($value);
        }
    }

    /**
     * Batch adding product properties
     *
     * @param array<int, array> $productProperties
     *
     * @return Generator<int, AddedProductPropertyBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'catalog.productProperty.add',
        'https://apidocs.bitrix24.com/api-reference/catalog/product-property/catalog-product-property-add.html',
        'Batch adding product properties'
    )]
    public function add(array $productProperties): Generator
    {
        $items = [];
        foreach ($productProperties as $item) {
            $items[] = ['fields' => $item];
        }

        foreach ($this->batch->addEntityItems('catalog.productProperty.add', $items) as $key => $item) {
            yield $key => new AddedProductPropertyBatchResult($item);
        }
    }

    /**
     * Batch update product properties
     *
     * Update elements in array with structure:
     * id => [  // Property id
     *     'fields' => [] // Property fields to update, must include iblockId
     * ]
     *
     * @param array<int, array> $entityItems
     *
     * @return Generator<int, UpdatedProductPropertyBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'catalog.productProperty.update',
        'https://apidocs.bitrix24.com/api-reference/catalog/product-property/catalog-product-property-update.html',
        'Batch update product properties'
    )]
    public function update(array $entityItems): Generator
    {
        foreach ($this->batch->updateEntityItems('catalog.productProperty.update', $entityItems) as $key => $item) {
            yield $key => new UpdatedProductPropertyBatchResult($item);
        }
    }

    /**
     * Batch delete product properties
     *
     * @param int[] $productPropertyId
     *
     * @return Generator<int, DeletedProductPropertyBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'catalog.productProperty.delete',
        'https://apidocs.bitrix24.com/api-reference/catalog/product-property/catalog-product-property-delete.html',
        'Batch delete product properties'
    )]
    public function delete(array $productPropertyId): Generator
    {
        foreach ($this->batch->deleteEntityItems('catalog.productProperty.delete', $productPropertyId) as $key => $item) {
            yield $key => new DeletedProductPropertyBatchResult($item);
        }
    }
}
