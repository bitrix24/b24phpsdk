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

namespace Bitrix24\SDK\Services\Sale\Shipment\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Services\Sale\Shipment\Result\AddedShipmentBatchResult;
use Bitrix24\SDK\Services\Sale\Shipment\Result\ShipmentItemResult;
use Bitrix24\SDK\Services\Sale\Shipment\Result\UpdatedShipmentBatchResult;
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
     * Batch list method for shipments
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/shipment/sale-shipment-list.html
     *
     * @param array $select Fields to select
     * @param array $filter Filter criteria
     * @param array $order  Sort order
     * @param int|null $limit Maximum number of items to return
     *
     * @return Generator<int, ShipmentItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.shipment.list',
        'https://apidocs.bitrix24.com/api-reference/sale/shipment/sale-shipment-list.html',
        'Batch list method for shipments'
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

        foreach ($this->batch->getTraversableList('sale.shipment.list', $order, $filter, $select, $limit) as $key => $value) {
            yield $key => new ShipmentItemResult($value);
        }
    }

    /**
     * Batch adding shipments
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/shipment/sale-shipment-add.html
     *
     * @param array<int, array> $shipments Array of shipment fields
     *
     * @return Generator<int, AddedShipmentBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.shipment.add',
        'https://apidocs.bitrix24.com/api-reference/sale/shipment/sale-shipment-add.html',
        'Batch adding shipments'
    )]
    public function add(array $shipments): Generator
    {
        $items = [];
        foreach ($shipments as $shipment) {
            $items[] = ['fields' => $shipment];
        }

        foreach ($this->batch->addEntityItems('sale.shipment.add', $items) as $key => $item) {
            yield $key => new AddedShipmentBatchResult($item);
        }
    }

    /**
     * Batch update shipments
     *
     * Update elements in array with structure
     * element_id => [ // shipment id
     *  // shipment fields to update
     * ]
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/shipment/sale-shipment-update.html
     *
     * @param array<int, array> $shipments keyed by shipment id
     *
     * @return Generator<int, UpdatedShipmentBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.shipment.update',
        'https://apidocs.bitrix24.com/api-reference/sale/shipment/sale-shipment-update.html',
        'Batch update shipments'
    )]
    public function update(array $shipments): Generator
    {
        $items = [];
        foreach ($shipments as $id => $shipment) {
            $items[$id] = ['fields' => $shipment];
        }

        foreach ($this->batch->updateEntityItems('sale.shipment.update', $items) as $key => $item) {
            yield $key => new UpdatedShipmentBatchResult($item);
        }
    }

    /**
     * Batch delete shipments
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/shipment/sale-shipment-delete.html
     *
     * @param int[] $shipmentId
     *
     * @return Generator<int, DeletedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.shipment.delete',
        'https://apidocs.bitrix24.com/api-reference/sale/shipment/sale-shipment-delete.html',
        'Batch delete shipments'
    )]
    public function delete(array $shipmentId): Generator
    {
        foreach ($this->batch->deleteEntityItems('sale.shipment.delete', $shipmentId) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }
}
