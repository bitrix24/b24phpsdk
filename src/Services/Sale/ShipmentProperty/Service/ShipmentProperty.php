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

namespace Bitrix24\SDK\Services\Sale\ShipmentProperty\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Sale\ShipmentProperty\Result\AddedShipmentPropertyResult;
use Bitrix24\SDK\Services\Sale\ShipmentProperty\Result\ShipmentPropertyFieldsResult;
use Bitrix24\SDK\Services\Sale\ShipmentProperty\Result\ShipmentPropertiesResult;
use Bitrix24\SDK\Services\Sale\ShipmentProperty\Result\ShipmentPropertyResult;
use Bitrix24\SDK\Services\Sale\ShipmentProperty\Result\UpdatedShipmentPropertyResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['sale']))]
class ShipmentProperty extends AbstractService
{
    public function __construct(CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Adds a shipment property.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/shipment-property/sale-shipment-property-add.html
     *
     * @param array $fields Field values for creating a shipment property. Required fields:
     *                      - name (string) - Name of the property
     *                      - type (string) - Type of the property
     *                      - required (string) - Whether the property is required ('Y'/'N')
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.shipmentproperty.add',
        'https://apidocs.bitrix24.com/api-reference/sale/shipment-property/sale-shipment-property-add.html',
        'Adds a shipment property.'
    )]
    public function add(array $fields): AddedShipmentPropertyResult
    {
        return new AddedShipmentPropertyResult(
            $this->core->call('sale.shipmentproperty.add', [
                'fields' => $fields
            ])
        );
    }

    /**
     * Updates the fields of a shipment property.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/shipment-property/sale-shipment-property-update.html
     *
     * @param int   $id     Shipment property identifier
     * @param array $fields Field values for update. At least one field must be provided.
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.shipmentproperty.update',
        'https://apidocs.bitrix24.com/api-reference/sale/shipment-property/sale-shipment-property-update.html',
        'Updates the fields of a shipment property.'
    )]
    public function update(int $id, array $fields): UpdatedShipmentPropertyResult
    {
        return new UpdatedShipmentPropertyResult(
            $this->core->call('sale.shipmentproperty.update', [
                'id' => $id,
                'fields' => $fields
            ])
        );
    }

    /**
     * Returns the value of a shipment property by its identifier.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/shipment-property/sale-shipment-property-get.html
     *
     * @param int $id Shipment property identifier
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.shipmentproperty.get',
        'https://apidocs.bitrix24.com/api-reference/sale/shipment-property/sale-shipment-property-get.html',
        'Returns the shipment property.'
    )]
    public function get(int $id): ShipmentPropertyResult
    {
        return new ShipmentPropertyResult(
            $this->core->call('sale.shipmentproperty.get', [
                'id' => $id
            ])
        );
    }

    /**
     * Returns a list of shipment properties.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/shipment-property/sale-shipment-property-list.html
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
        'sale.shipmentproperty.list',
        'https://apidocs.bitrix24.com/api-reference/sale/shipment-property/sale-shipment-property-list.html',
        'Returns a list of shipment properties.'
    )]
    public function list(array $select = [], array $filter = [], array $order = [], int $start = 0): ShipmentPropertiesResult
    {
        return new ShipmentPropertiesResult(
            $this->core->call('sale.shipmentproperty.list', [
                'select' => $select,
                'filter' => $filter,
                'order' => $order,
                'start' => $start
            ])
        );
    }

    /**
     * Deletes a shipment property.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/shipment-property/sale-shipment-property-delete.html
     *
     * @param int $id Shipment property identifier
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.shipmentproperty.delete',
        'https://apidocs.bitrix24.com/api-reference/sale/shipment-property/sale-shipment-property-delete.html',
        'Deletes a shipment property.'
    )]
    public function delete(int $id): DeletedItemResult
    {
        return new DeletedItemResult(
            $this->core->call('sale.shipmentproperty.delete', [
                'id' => $id
            ])
        );
    }

    /**
     * Returns the fields and settings for shipment properties of a specific property type.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/shipment-property/sale-shipment-property-get-fields-by-type.html
     *
     * @param string $type Shipment property type. Possible values: STRING, Y/N, NUMBER, ENUM, FILE, DATE, LOCATION, ADDRESS
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.shipmentproperty.getFieldsByType',
        'https://apidocs.bitrix24.com/api-reference/sale/shipment-property/sale-shipment-property-get-fields-by-type.html',
        'Returns the fields and settings for shipment properties.'
    )]
    public function getFieldsByType(string $type): ShipmentPropertyFieldsResult
    {
        return new ShipmentPropertyFieldsResult(
            $this->core->call('sale.shipmentproperty.getFieldsByType', [
                'type' => $type
            ])
        );
    }
}
