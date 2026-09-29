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

namespace Bitrix24\SDK\Services\Sale\PropertyVariant\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Services\Sale\PropertyVariant\Result\PropertyVariantAddBatchResult;
use Bitrix24\SDK\Services\Sale\PropertyVariant\Result\PropertyVariantItemResult;
use Bitrix24\SDK\Services\Sale\PropertyVariant\Result\PropertyVariantUpdateBatchResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['sale']))]
class Batch
{
    /**
     * Batch constructor
     */
    public function __construct(protected BatchOperationsInterface $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch list method for property variants
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/property-variant/sale-property-variant-list.html
     *
     * @param array $select Fields to select
     * @param array $filter Filter criteria
     * @param array $order  Sort order
     * @param int|null $limit Maximum number of items to return
     *
     * @return Generator<int, PropertyVariantItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.propertyvariant.list',
        'https://apidocs.bitrix24.com/api-reference/sale/property-variant/sale-property-variant-list.html',
        'Batch list method for property variants'
    )]
    public function list(array $select = [], array $filter = [], array $order = [], ?int $limit = null): Generator
    {
        $this->log->debug(
            'batchList',
            [
                'select' => $select,
                'filter' => $filter,
                'order' => $order,
                'limit' => $limit,
            ]
        );

        foreach ($this->batch->getTraversableList('sale.propertyvariant.list', $order, $filter, $select, $limit) as $key => $value) {
            yield $key => new PropertyVariantItemResult($value);
        }
    }

    /**
     * Batch adding property variants
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/property-variant/sale-property-variant-add.html
     *
     * @param array<int, array> $propertyVariants Array of property variant fields
     *
     * @return Generator<int, PropertyVariantAddBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.propertyvariant.add',
        'https://apidocs.bitrix24.com/api-reference/sale/property-variant/sale-property-variant-add.html',
        'Batch adding property variants'
    )]
    public function add(array $propertyVariants): Generator
    {
        $items = [];
        foreach ($propertyVariants as $propertyVariant) {
            $items[] = ['fields' => $propertyVariant];
        }

        foreach ($this->batch->addEntityItems('sale.propertyvariant.add', $items) as $key => $item) {
            yield $key => new PropertyVariantAddBatchResult($item);
        }
    }

    /**
     * Batch update property variants
     *
     * Update elements in array with structure
     * element_id => [ // property variant id
     *  // property variant fields to update
     * ]
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/property-variant/sale-property-variant-update.html
     *
     * @param array<int, array> $propertyVariants keyed by property variant id
     *
     * @return Generator<int, PropertyVariantUpdateBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.propertyvariant.update',
        'https://apidocs.bitrix24.com/api-reference/sale/property-variant/sale-property-variant-update.html',
        'Batch update property variants'
    )]
    public function update(array $propertyVariants): Generator
    {
        $items = [];
        foreach ($propertyVariants as $id => $propertyVariant) {
            $items[$id] = ['fields' => $propertyVariant];
        }

        foreach ($this->batch->updateEntityItems('sale.propertyvariant.update', $items) as $key => $item) {
            yield $key => new PropertyVariantUpdateBatchResult($item);
        }
    }

    /**
     * Batch delete property variants
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/property-variant/sale-property-variant-delete.html
     *
     * @param int[] $propertyVariantId
     *
     * @return Generator<int, DeletedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.propertyvariant.delete',
        'https://apidocs.bitrix24.com/api-reference/sale/property-variant/sale-property-variant-delete.html',
        'Batch delete property variants'
    )]
    public function delete(array $propertyVariantId): Generator
    {
        foreach ($this->batch->deleteEntityItems('sale.propertyvariant.delete', $propertyVariantId) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }
}
