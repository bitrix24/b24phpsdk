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

namespace Bitrix24\SDK\Services\Sale\ShipmentItem\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Services\Sale\ShipmentItem\Result\AddedShipmentItemBatchResult;
use Bitrix24\SDK\Services\Sale\ShipmentItem\Result\ShipmentItemItemResult;
use Bitrix24\SDK\Services\Sale\ShipmentItem\Result\UpdatedShipmentItemBatchResult;
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
     * Batch list method for shipment items
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/shipment-item/sale-shipment-item-list.html
     *
     * @param array $select Fields to select
     * @param array $filter Filter criteria
     * @param array $order  Sort order
     * @param int|null $limit Maximum number of items to return
     *
     * @return Generator<int, ShipmentItemItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.shipmentitem.list',
        'https://apidocs.bitrix24.com/api-reference/sale/shipment-item/sale-shipment-item-list.html',
        'Batch list method for shipment items'
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

        foreach ($this->batch->getTraversableList('sale.shipmentitem.list', $order, $filter, $select, $limit) as $key => $value) {
            yield $key => new ShipmentItemItemResult($value);
        }
    }

    /**
     * Batch adding shipment items
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/shipment-item/sale-shipment-item-add.html
     *
     * @param array<int, array> $shipmentItems Array of shipment item fields
     *
     * @return Generator<int, AddedShipmentItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.shipmentitem.add',
        'https://apidocs.bitrix24.com/api-reference/sale/shipment-item/sale-shipment-item-add.html',
        'Batch adding shipment items'
    )]
    public function add(array $shipmentItems): Generator
    {
        $items = [];
        foreach ($shipmentItems as $shipmentItem) {
            $items[] = ['fields' => $shipmentItem];
        }

        foreach ($this->batch->addEntityItems('sale.shipmentitem.add', $items) as $key => $item) {
            yield $key => new AddedShipmentItemBatchResult($item);
        }
    }

    /**
     * Batch update shipment items
     *
     * Update elements in array with structure
     * element_id => [ // shipment item id
     *  // shipment item fields to update
     * ]
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/shipment-item/sale-shipment-item-update.html
     *
     * @param array<int, array> $shipmentItems keyed by shipment item id
     *
     * @return Generator<int, UpdatedShipmentItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.shipmentitem.update',
        'https://apidocs.bitrix24.com/api-reference/sale/shipment-item/sale-shipment-item-update.html',
        'Batch update shipment items'
    )]
    public function update(array $shipmentItems): Generator
    {
        $items = [];
        foreach ($shipmentItems as $id => $shipmentItem) {
            $items[$id] = ['fields' => $shipmentItem];
        }

        foreach ($this->batch->updateEntityItems('sale.shipmentitem.update', $items) as $key => $item) {
            yield $key => new UpdatedShipmentItemBatchResult($item);
        }
    }

    /**
     * Batch delete shipment items
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/shipment-item/sale-shipment-item-delete.html
     *
     * @param int[] $shipmentItemId
     *
     * @return Generator<int, DeletedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.shipmentitem.delete',
        'https://apidocs.bitrix24.com/api-reference/sale/shipment-item/sale-shipment-item-delete.html',
        'Batch delete shipment items'
    )]
    public function delete(array $shipmentItemId): Generator
    {
        foreach ($this->batch->deleteEntityItems('sale.shipmentitem.delete', $shipmentItemId) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }
}
