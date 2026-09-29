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

namespace Bitrix24\SDK\Services\Sale\ShipmentProperty\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Services\Sale\ShipmentProperty\Result\AddedShipmentPropertyBatchResult;
use Bitrix24\SDK\Services\Sale\ShipmentProperty\Result\ShipmentPropertyItemResult;
use Bitrix24\SDK\Services\Sale\ShipmentProperty\Result\UpdatedShipmentPropertyBatchResult;
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
     * Batch list method for shipment properties
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/shipment-property/sale-shipment-property-list.html
     *
     * @param array $select Fields to select
     * @param array $filter Filter criteria
     * @param array $order  Sort order
     * @param int|null $limit Maximum number of items to return
     *
     * @return Generator<int, ShipmentPropertyItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.shipmentproperty.list',
        'https://apidocs.bitrix24.com/api-reference/sale/shipment-property/sale-shipment-property-list.html',
        'Batch list method for shipment properties'
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

        foreach ($this->batch->getTraversableList('sale.shipmentproperty.list', $order, $filter, $select, $limit) as $key => $value) {
            yield $key => new ShipmentPropertyItemResult($value);
        }
    }

    /**
     * Batch adding shipment properties
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/shipment-property/sale-shipment-property-add.html
     *
     * @param array<int, array> $properties Array of shipment property fields
     *
     * @return Generator<int, AddedShipmentPropertyBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.shipmentproperty.add',
        'https://apidocs.bitrix24.com/api-reference/sale/shipment-property/sale-shipment-property-add.html',
        'Batch adding shipment properties'
    )]
    public function add(array $properties): Generator
    {
        $items = [];
        foreach ($properties as $property) {
            $items[] = ['fields' => $property];
        }

        foreach ($this->batch->addEntityItems('sale.shipmentproperty.add', $items) as $key => $item) {
            yield $key => new AddedShipmentPropertyBatchResult($item);
        }
    }

    /**
     * Batch update shipment properties
     *
     * Update elements in array with structure
     * element_id => [ // shipment property id
     *  // shipment property fields to update
     * ]
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/shipment-property/sale-shipment-property-update.html
     *
     * @param array<int, array> $properties keyed by shipment property id
     *
     * @return Generator<int, UpdatedShipmentPropertyBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.shipmentproperty.update',
        'https://apidocs.bitrix24.com/api-reference/sale/shipment-property/sale-shipment-property-update.html',
        'Batch update shipment properties'
    )]
    public function update(array $properties): Generator
    {
        $items = [];
        foreach ($properties as $id => $property) {
            $items[$id] = ['fields' => $property];
        }

        foreach ($this->batch->updateEntityItems('sale.shipmentproperty.update', $items) as $key => $item) {
            yield $key => new UpdatedShipmentPropertyBatchResult($item);
        }
    }

    /**
     * Batch delete shipment properties
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/shipment-property/sale-shipment-property-delete.html
     *
     * @param int[] $propertyId
     *
     * @return Generator<int, DeletedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.shipmentproperty.delete',
        'https://apidocs.bitrix24.com/api-reference/sale/shipment-property/sale-shipment-property-delete.html',
        'Batch delete shipment properties'
    )]
    public function delete(array $propertyId): Generator
    {
        foreach ($this->batch->deleteEntityItems('sale.shipmentproperty.delete', $propertyId) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }
}
