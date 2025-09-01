<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Sally Fancen <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Sale\Order\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AddedItemBatchResult;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Core\Result\UpdatedItemBatchResult;
use Bitrix24\SDK\Services\Sale\Order\Result\OrderItemResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['sale']))]
class Batch
{
    /**
     * Batch constructor.
     */
    public function __construct(protected BatchOperationsInterface $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch adding orders
     *
     * @return Generator<int, AddedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.order.add',
        'https://apidocs.bitrix24.com/api-reference/sale/order/sale-order-add.html',
        'Batch adding orders'
    )]
    public function add(array $orders): Generator
    {
        foreach ($this->batch->addEntityItems('sale.order.add', $orders) as $key => $item) {
            yield $key => new AddedItemBatchResult($item);
        }
    }

    /**
     * Batch update orders
     *
     * Update elements in array with structure
     * element_id => [  // id
     *  'fields' => [] // fields to update
     * ]
     *
     * @param array<int, array> $orderItems
     * @return Generator<int, UpdatedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.order.update',
        'https://apidocs.bitrix24.com/api-reference/sale/order/sale-order-update.html',
        'Update in batch mode a list of orders'
    )]
    public function update(array $orderItems): Generator
    {
        foreach ($this->batch->updateEntityItems('sale.order.update', $orderItems) as $key => $item) {
            yield $key => new UpdatedItemBatchResult($item);
        }
    }

    /**
     * Batch delete orders
     *
     * @param int[] $orderIds
     *
     * @return Generator<int, DeletedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.order.delete',
        'https://apidocs.bitrix24.com/api-reference/sale/order/sale-order-delete.html',
        'Batch delete orders'
    )]
    public function delete(array $orderIds): Generator
    {
        foreach ($this->batch->deleteEntityItems('sale.order.delete', $orderIds) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }
    
    /**
     * Batch list method for orders
     *
     * @return Generator<int, DepartmentItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.order.list',
        'https://apidocs.bitrix24.com/api-reference/sale/order/sale-order-list.html',
        'Batch list method for orders'
    )]
    public function list(array $filter = [], array $order = [], array $select = [], ?int $limit = null): Generator
    {
        $this->log->debug(
            'batchList',
            [
                'order'  => $order,
                'filter' => $filter,
                'select' => $select,
                'limit'  => $limit,
            ]
        );
        foreach ($this->batch->getTraversableList('sale.order.list', $order, $filter, $select, $limit) as $key => $value) {
            yield $key => new OrderItemResult($value);
        }
    }
}
