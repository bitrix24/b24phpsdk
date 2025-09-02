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
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Services\Sale\Order\Result\UpdatedOrderBatchResult;
use Bitrix24\SDK\Services\Sale\Order\Result\AddedOrderBatchResult;
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
     * Batch add method for creating multiple orders
     *
     * @param array $orders Array of order fields
     *
     * @return Generator<int, AddedOrderBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.order.add',
        'https://apidocs.bitrix24.com/api-reference/sale/order/sale-order-add.html',
        'Creates a new order'
    )]
    public function add(array $orders): Generator
    {
        $fields = [];
        foreach ($orders as $orderFields) {
            $fields[] = [
                'fields' => $orderFields
            ];
        }
        
        foreach ($this->batch->addEntityItems('sale.order.add', $fields) as $key => $item) {
            yield $key => new AddedOrderBatchResult($item);
        }
    }

    /**
     * Batch update method for updating multiple orders
     *
     * Update elements in array with structure
     * element_id => [  // order id
     *  'fields' => [] // order fields to update
     * ]
     *
     * @param array<int, array> $ordersData
     * @return Generator<int, UpdatedOrderBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.order.update',
        'https://apidocs.bitrix24.com/api-reference/sale/order/sale-order-update.html',
        'Updates an existing order'
    )]
    public function update(array $ordersData): Generator
    {
        foreach ($this->batch->updateEntityItems('sale.order.update', $ordersData) as $key => $item) {
            yield $key => new UpdatedOrderBatchResult($item);
        }
    }

    /**
     * Batch list method for retrieving multiple orders
     *
     * @param array $filter Filter criteria
     * @param array $order Sort order
     * @param array $select Fields to select
     * @param int|null $limit Maximum number of items to return
     *
     * @return Generator|OrderItemResult[]
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.order.list',
        'https://apidocs.bitrix24.com/api-reference/sale/order/sale-order-list.html',
        'Retrieves a list of orders'
    )]
    public function list(array $filter = [], array $order = [], array $select = [], ?int $limit = null): Generator
    {
        foreach ($this->batch->getTraversableList(
            'sale.order.list',
            $order,
            $filter,
            $select,
            $limit
        ) as $key => $item) {
            yield $key => new OrderItemResult($item);
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
    #[ApiEndpointMetadata(
        'sale.order.delete',
        'https://apidocs.bitrix24.com/api-reference/sale/order/sale-order-delete.html',
        'Batch delete orders.'
    )]
    public function delete(array $orderIds): Generator
    {
        foreach ($this->batch->deleteEntityItems('sale.order.delete', $orderIds) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }
}
