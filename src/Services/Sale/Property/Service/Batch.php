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

namespace Bitrix24\SDK\Services\Sale\Property\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Services\Sale\Property\Result\PropertyAddBatchResult;
use Bitrix24\SDK\Services\Sale\Property\Result\PropertyItemResult;
use Bitrix24\SDK\Services\Sale\Property\Result\PropertyUpdateBatchResult;
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
     * Batch list method for order properties
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/property/sale-property-list.html
     *
     * @param array $select Fields to select
     * @param array $filter Filter criteria
     * @param array $order  Sort order
     * @param int|null $limit Maximum number of items to return
     *
     * @return Generator<int, PropertyItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.property.list',
        'https://apidocs.bitrix24.com/api-reference/sale/property/sale-property-list.html',
        'Batch list method for order properties'
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

        foreach ($this->batch->getTraversableList('sale.property.list', $order, $filter, $select, $limit) as $key => $value) {
            yield $key => new PropertyItemResult($value);
        }
    }

    /**
     * Batch adding order properties
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/property/sale-property-add.html
     *
     * @param array<int, array> $properties Array of order property fields
     *
     * @return Generator<int, PropertyAddBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.property.add',
        'https://apidocs.bitrix24.com/api-reference/sale/property/sale-property-add.html',
        'Batch adding order properties'
    )]
    public function add(array $properties): Generator
    {
        $items = [];
        foreach ($properties as $property) {
            $items[] = ['fields' => $property];
        }

        foreach ($this->batch->addEntityItems('sale.property.add', $items) as $key => $item) {
            yield $key => new PropertyAddBatchResult($item);
        }
    }

    /**
     * Batch update order properties
     *
     * Update elements in array with structure
     * element_id => [ // order property id
     *  // order property fields to update
     * ]
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/property/sale-property-update.html
     *
     * @param array<int, array> $properties keyed by order property id
     *
     * @return Generator<int, PropertyUpdateBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.property.update',
        'https://apidocs.bitrix24.com/api-reference/sale/property/sale-property-update.html',
        'Batch update order properties'
    )]
    public function update(array $properties): Generator
    {
        $items = [];
        foreach ($properties as $id => $property) {
            $items[$id] = ['fields' => $property];
        }

        foreach ($this->batch->updateEntityItems('sale.property.update', $items) as $key => $item) {
            yield $key => new PropertyUpdateBatchResult($item);
        }
    }

    /**
     * Batch delete order properties
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/property/sale-property-delete.html
     *
     * @param int[] $propertyId
     *
     * @return Generator<int, DeletedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.property.delete',
        'https://apidocs.bitrix24.com/api-reference/sale/property/sale-property-delete.html',
        'Batch delete order properties'
    )]
    public function delete(array $propertyId): Generator
    {
        foreach ($this->batch->deleteEntityItems('sale.property.delete', $propertyId) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }
}
