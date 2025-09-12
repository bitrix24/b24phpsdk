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

namespace Bitrix24\SDK\Services\Sale\Shipment\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Sale\Shipment\Result\AddedShipmentResult;
use Bitrix24\SDK\Services\Sale\Shipment\Result\ShipmentFieldsResult;
use Bitrix24\SDK\Services\Sale\Shipment\Result\ShipmentsResult;
use Bitrix24\SDK\Services\Sale\Shipment\Result\ShipmentResult;
use Bitrix24\SDK\Services\Sale\Shipment\Result\UpdatedShipmentResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['sale']))]
class Shipment extends AbstractService
{
    public function __construct(CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Adds a shipment.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/shipment/sale-shipment-add.html
     *
     * @param array $fields Field values for creating a shipment. Required fields:
     *                      - orderId (int) - Order ID to which the shipment belongs
     *                      - deliveryId (int) - Delivery service ID
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.shipment.add',
        'https://apidocs.bitrix24.com/api-reference/sale/shipment/sale-shipment-add.html',
        'Adds a shipment.'
    )]
    public function add(array $fields): AddedShipmentResult
    {
        return new AddedShipmentResult(
            $this->core->call('sale.shipment.add', [
                'fields' => $fields
            ])
        );
    }

    /**
     * Updates the fields of a shipment.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/shipment/sale-shipment-update.html
     *
     * @param int   $id     Shipment identifier
     * @param array $fields Field values for update. Required fields:
     *                      - deliveryId (int) - Delivery service ID
     *                      - allowDelivery (string) - Allow delivery status ('Y'/'N')
     *                      - deducted (string) - Deducted status ('Y'/'N')
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.shipment.update',
        'https://apidocs.bitrix24.com/api-reference/sale/shipment/sale-shipment-update.html',
        'Updates the fields of a shipment.'
    )]
    public function update(int $id, array $fields): UpdatedShipmentResult
    {
        return new UpdatedShipmentResult(
            $this->core->call('sale.shipment.update', [
                'id' => $id,
                'fields' => $fields
            ])
        );
    }

    /**
     * Returns the value of a shipment by its identifier.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/shipment/sale-shipment-get.html
     *
     * @param int $id Shipment identifier
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.shipment.get',
        'https://apidocs.bitrix24.com/api-reference/sale/shipment/sale-shipment-get.html',
        'Returns the shipment.'
    )]
    public function get(int $id): ShipmentResult
    {
        return new ShipmentResult(
            $this->core->call('sale.shipment.get', [
                'id' => $id
            ])
        );
    }

    /**
     * Returns a list of shipments.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/shipment/sale-shipment-list.html
     *
     * @param array $select Fields to select
     * @param array $filter Filter criteria
     * @param array $order  Sort order
     * @param int   $start  Pagination start (offset)
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.shipment.list',
        'https://apidocs.bitrix24.com/api-reference/sale/shipment/sale-shipment-list.html',
        'Returns a list of shipments.'
    )]
    public function list(array $select = [], array $filter = [], array $order = [], int $start = 0): ShipmentsResult
    {
        return new ShipmentsResult(
            $this->core->call('sale.shipment.list', [
                'select' => $select,
                'filter' => $filter,
                'order' => $order,
                'start' => $start
            ])
        );
    }

    /**
     * Deletes a shipment.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/shipment/sale-shipment-delete.html
     *
     * @param int $id Shipment identifier
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.shipment.delete',
        'https://apidocs.bitrix24.com/api-reference/sale/shipment/sale-shipment-delete.html',
        'Deletes a shipment.'
    )]
    public function delete(int $id): DeletedItemResult
    {
        return new DeletedItemResult(
            $this->core->call('sale.shipment.delete', [
                'id' => $id
            ])
        );
    }

    /**
     * Returns the fields and settings for shipments.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/shipment/sale-shipment-get-fields.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.shipment.getFields',
        'https://apidocs.bitrix24.com/api-reference/sale/shipment/sale-shipment-get-fields.html',
        'Returns the fields and settings for shipments.'
    )]
    public function getFields(): ShipmentFieldsResult
    {
        return new ShipmentFieldsResult(
            $this->core->call('sale.shipment.getFields', [])
        );
    }
}
