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

namespace Bitrix24\SDK\Services\Catalog\Product\ProductService\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Services\Catalog\Product\ProductService\Result\ProductServiceAddedBatchResult;
use Bitrix24\SDK\Services\Catalog\Product\ProductService\Result\ProductServiceItemResult;
use Bitrix24\SDK\Services\Catalog\Product\ProductService\Result\ProductServiceUpdatedBatchResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['catalog']))]
class Batch
{
    public function __construct(protected BatchOperationsInterface $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch list of commercial catalog services
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/product/service/catalog-product-service-list.html
     *
     * @param array<string, 'asc'|'desc'|'ASC'|'DESC'>           $order
     * @param array<string, scalar|array{0?: scalar, 1?:scalar}> $filter
     * @param array<int, string>                                 $select
     *
     * @return Generator<int, ProductServiceItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'catalog.product.service.list',
        'https://apidocs.bitrix24.com/api-reference/catalog/product/service/catalog-product-service-list.html',
        'Batch list of commercial catalog services'
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
                'catalog.product.service.list',
                $order,
                $filter,
                $select,
                $limit
            ) as $key => $value
        ) {
            yield $key => new ProductServiceItemResult($value);
        }
    }

    /**
     * Batch adding commercial catalog services
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/product/service/catalog-product-service-add.html
     *
     * @param array<int, array> $services
     *
     * @return Generator<int, ProductServiceAddedBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'catalog.product.service.add',
        'https://apidocs.bitrix24.com/api-reference/catalog/product/service/catalog-product-service-add.html',
        'Batch adding commercial catalog services'
    )]
    public function add(array $services): Generator
    {
        $items = [];
        foreach ($services as $service) {
            $items[] = ['fields' => $service];
        }

        foreach ($this->batch->addEntityItems('catalog.product.service.add', $items) as $key => $item) {
            yield $key => new ProductServiceAddedBatchResult($item);
        }
    }

    /**
     * Batch update commercial catalog services
     *
     * Update elements in array with structure:
     * id => [
     *   'fields' => [] // fields to update
     * ]
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/product/service/catalog-product-service-update.html
     *
     * @param array<int, array{fields: array}> $entityItems
     *
     * @return Generator<int, ProductServiceUpdatedBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'catalog.product.service.update',
        'https://apidocs.bitrix24.com/api-reference/catalog/product/service/catalog-product-service-update.html',
        'Batch update commercial catalog services'
    )]
    public function update(array $entityItems): Generator
    {
        foreach ($this->batch->updateEntityItems('catalog.product.service.update', $entityItems) as $key => $item) {
            yield $key => new ProductServiceUpdatedBatchResult($item);
        }
    }

    /**
     * Batch delete commercial catalog services
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/product/service/catalog-product-service-delete.html
     *
     * @param int[] $serviceIds
     *
     * @return Generator<int, DeletedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'catalog.product.service.delete',
        'https://apidocs.bitrix24.com/api-reference/catalog/product/service/catalog-product-service-delete.html',
        'Batch delete commercial catalog services'
    )]
    public function delete(array $serviceIds): Generator
    {
        foreach ($this->batch->deleteEntityItems('catalog.product.service.delete', $serviceIds) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }
}
