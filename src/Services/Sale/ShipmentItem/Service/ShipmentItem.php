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

namespace Bitrix24\SDK\Services\Sale\ShipmentItem\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Sale\ShipmentItem\Result\AddedShipmentItemResult;
use Bitrix24\SDK\Services\Sale\ShipmentItem\Result\ShipmentItemFieldsResult;
use Bitrix24\SDK\Services\Sale\ShipmentItem\Result\ShipmentItemResult;
use Bitrix24\SDK\Services\Sale\ShipmentItem\Result\ShipmentItemsResult;
use Bitrix24\SDK\Services\Sale\ShipmentItem\Result\UpdatedShipmentItemResult;
use Psr\Log\LoggerInterface;

class ShipmentItem extends AbstractService
{
    public function __construct(
        CoreInterface $core,
        LoggerInterface $logger
    ) {
        parent::__construct($core, $logger);
    }

    /**
     * Add a new shipment item
     *
     * @param array $fields Shipment item fields
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.shipmentitem.add',
        'https://apidocs.bitrix24.com/api-reference/sale/shipment-item/sale-shipment-item-add.html',
        'Add a new shipment item'
    )]
    public function add(array $fields): AddedShipmentItemResult
    {
        return new AddedShipmentItemResult(
            $this->core->call(
                'sale.shipmentitem.add',
                [
                    'fields' => $fields,
                ]
            )
        );
    }

    /**
     * Update shipment item
     *
     * @param int $id Shipment item identifier
     * @param array $fields Fields to update
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.shipmentitem.update',
        'https://apidocs.bitrix24.com/api-reference/sale/shipment-item/sale-shipment-item-update.html',
        'Update shipment item'
    )]
    public function update(int $id, array $fields): UpdatedShipmentItemResult
    {
        return new UpdatedShipmentItemResult(
            $this->core->call(
                'sale.shipmentitem.update',
                [
                    'id' => $id,
                    'fields' => $fields,
                ]
            )
        );
    }

    /**
     * Get shipment item by ID
     *
     * @param int $id Shipment item identifier
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.shipmentitem.get',
        'https://apidocs.bitrix24.com/api-reference/sale/shipment-item/sale-shipment-item-get.html',
        'Get shipment item by ID'
    )]
    public function get(int $id): ShipmentItemResult
    {
        return new ShipmentItemResult(
            $this->core->call(
                'sale.shipmentitem.get',
                [
                    'id' => $id,
                ]
            )
        );
    }

    /**
     * Get list of shipment items
     *
     * @param array $select Fields to select
     * @param array $filter Filter conditions
     * @param array $order Sort order
     * @param int $start Offset for pagination
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.shipmentitem.list',
        'https://apidocs.bitrix24.com/api-reference/sale/shipment-item/sale-shipment-item-list.html',
        'Get list of shipment items'
    )]
    public function list(array $select = [], array $filter = [], array $order = [], int $start = 0): ShipmentItemsResult
    {
        return new ShipmentItemsResult(
            $this->core->call(
                'sale.shipmentitem.list',
                [
                    'select' => $select,
                    'filter' => $filter,
                    'order' => $order,
                    'start' => $start,
                ]
            )
        );
    }

    /**
     * Delete shipment item
     *
     * @param int $id Shipment item identifier
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.shipmentitem.delete',
        'https://apidocs.bitrix24.com/api-reference/sale/shipment-item/sale-shipment-item-delete.html',
        'Delete shipment item'
    )]
    public function delete(int $id): DeletedItemResult
    {
        return new DeletedItemResult(
            $this->core->call(
                'sale.shipmentitem.delete',
                [
                    'id' => $id,
                ]
            )
        );
    }

    /**
     * Get fields description for shipment items
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.shipmentitem.getfields',
        'https://apidocs.bitrix24.com/api-reference/sale/shipment-item/sale-shipment-item-get-fields.html',
        'Get fields description for shipment items'
    )]
    public function getFields(): ShipmentItemFieldsResult
    {
        return new ShipmentItemFieldsResult(
            $this->core->call(
                'sale.shipmentitem.getfields',
                []
            )
        );
    }
}
