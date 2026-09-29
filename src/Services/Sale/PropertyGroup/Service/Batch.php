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

namespace Bitrix24\SDK\Services\Sale\PropertyGroup\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Services\Sale\PropertyGroup\Result\PropertyGroupAddBatchResult;
use Bitrix24\SDK\Services\Sale\PropertyGroup\Result\PropertyGroupItemResult;
use Bitrix24\SDK\Services\Sale\PropertyGroup\Result\PropertyGroupUpdateBatchResult;
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
     * Batch list method for property groups
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/property-group/sale-property-group-list.html
     *
     * @param array $select Fields to select
     * @param array $filter Filter criteria
     * @param array $order  Sort order
     * @param int|null $limit Maximum number of items to return
     *
     * @return Generator<int, PropertyGroupItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.propertygroup.list',
        'https://apidocs.bitrix24.com/api-reference/sale/property-group/sale-property-group-list.html',
        'Batch list method for property groups'
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

        foreach ($this->batch->getTraversableList('sale.propertygroup.list', $order, $filter, $select, $limit) as $key => $value) {
            yield $key => new PropertyGroupItemResult($value);
        }
    }

    /**
     * Batch adding property groups
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/property-group/sale-property-group-add.html
     *
     * @param array<int, array> $propertyGroups Array of property group fields
     *
     * @return Generator<int, PropertyGroupAddBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.propertygroup.add',
        'https://apidocs.bitrix24.com/api-reference/sale/property-group/sale-property-group-add.html',
        'Batch adding property groups'
    )]
    public function add(array $propertyGroups): Generator
    {
        $items = [];
        foreach ($propertyGroups as $propertyGroup) {
            $items[] = ['fields' => $propertyGroup];
        }

        foreach ($this->batch->addEntityItems('sale.propertygroup.add', $items) as $key => $item) {
            yield $key => new PropertyGroupAddBatchResult($item);
        }
    }

    /**
     * Batch update property groups
     *
     * Update elements in array with structure
     * element_id => [ // property group id
     *  // property group fields to update
     * ]
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/property-group/sale-property-group-update.html
     *
     * @param array<int, array> $propertyGroups keyed by property group id
     *
     * @return Generator<int, PropertyGroupUpdateBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.propertygroup.update',
        'https://apidocs.bitrix24.com/api-reference/sale/property-group/sale-property-group-update.html',
        'Batch update property groups'
    )]
    public function update(array $propertyGroups): Generator
    {
        $items = [];
        foreach ($propertyGroups as $id => $propertyGroup) {
            $items[$id] = ['fields' => $propertyGroup];
        }

        foreach ($this->batch->updateEntityItems('sale.propertygroup.update', $items) as $key => $item) {
            yield $key => new PropertyGroupUpdateBatchResult($item);
        }
    }

    /**
     * Batch delete property groups
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/property-group/sale-property-group-delete.html
     *
     * @param int[] $propertyGroupId
     *
     * @return Generator<int, DeletedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.propertygroup.delete',
        'https://apidocs.bitrix24.com/api-reference/sale/property-group/sale-property-group-delete.html',
        'Batch delete property groups'
    )]
    public function delete(array $propertyGroupId): Generator
    {
        foreach ($this->batch->deleteEntityItems('sale.propertygroup.delete', $propertyGroupId) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }
}
