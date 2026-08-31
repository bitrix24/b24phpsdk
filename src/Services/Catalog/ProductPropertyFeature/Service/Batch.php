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

namespace Bitrix24\SDK\Services\Catalog\ProductPropertyFeature\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Services\Catalog\ProductPropertyFeature\Result\ProductPropertyFeatureAddedBatchResult;
use Bitrix24\SDK\Services\Catalog\ProductPropertyFeature\Result\ProductPropertyFeatureItemResult;
use Bitrix24\SDK\Services\Catalog\ProductPropertyFeature\Result\ProductPropertyFeatureUpdatedBatchResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['catalog']))]
class Batch
{
    public function __construct(protected BatchOperationsInterface $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch list product/variation property parameters.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/product-property-feature/catalog-product-property-feature-list.html
     *
     * @param array<string, 'asc'|'desc'|'ASC'|'DESC'>           $order
     * @param array<string, scalar|array{0?: scalar, 1?:scalar}> $filter
     * @param array<int, string>                                 $select
     *
     * @return Generator<int, ProductPropertyFeatureItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'catalog.productPropertyFeature.list',
        'https://apidocs.bitrix24.com/api-reference/catalog/product-property-feature/catalog-product-property-feature-list.html',
        'Batch list product/variation property parameters'
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
                'catalog.productPropertyFeature.list',
                $order,
                $filter,
                $select,
                $limit
            ) as $key => $value
        ) {
            yield $key => new ProductPropertyFeatureItemResult($value);
        }
    }

    /**
     * Batch add product/variation property parameters.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/product-property-feature/catalog-product-property-feature-add.html
     *
     * @param array<int, array{
     *   propertyId: int,
     *   moduleId: string,
     *   featureId: string,
     *   isEnabled: string,
     * }> $productPropertyFeatures
     *
     * @return Generator<int, ProductPropertyFeatureAddedBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'catalog.productPropertyFeature.add',
        'https://apidocs.bitrix24.com/api-reference/catalog/product-property-feature/catalog-product-property-feature-add.html',
        'Batch add product/variation property parameters'
    )]
    public function add(array $productPropertyFeatures): Generator
    {
        $items = [];
        foreach ($productPropertyFeatures as $item) {
            $items[] = [
                'fields' => $item,
            ];
        }

        foreach ($this->batch->addEntityItems('catalog.productPropertyFeature.add', $items) as $key => $item) {
            yield $key => new ProductPropertyFeatureAddedBatchResult($item);
        }
    }

    /**
     * Batch update product/variation property parameters.
     *
     * Update elements in array with structure:
     * id => [
     *   'fields' => [] // fields to update
     * ]
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/product-property-feature/catalog-product-property-feature-update.html
     *
     * @param array<int, array{fields: array}> $entityItems
     *
     * @return Generator<int, ProductPropertyFeatureUpdatedBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'catalog.productPropertyFeature.update',
        'https://apidocs.bitrix24.com/api-reference/catalog/product-property-feature/catalog-product-property-feature-update.html',
        'Batch update product/variation property parameters'
    )]
    public function update(array $entityItems): Generator
    {
        foreach (
            $this->batch->updateEntityItems(
                'catalog.productPropertyFeature.update',
                $entityItems
            ) as $key => $item
        ) {
            yield $key => new ProductPropertyFeatureUpdatedBatchResult($item);
        }
    }
}
