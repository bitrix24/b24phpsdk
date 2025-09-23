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

namespace Bitrix24\SDK\Services\Sale\ShipmentPropertyValue\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Sale\ShipmentPropertyValue\Result\ShipmentPropertyValueFieldsResult;
use Bitrix24\SDK\Services\Sale\ShipmentPropertyValue\Result\UpdatedShipmentPropertyValueResult;
use Bitrix24\SDK\Services\Sale\ShipmentPropertyValue\Result\ShipmentPropertyValueResult;
use Bitrix24\SDK\Services\Sale\ShipmentPropertyValue\Result\ShipmentPropertyValuesResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['sale']))]
class ShipmentPropertyValue extends AbstractService
{
    public function __construct(CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Updates shipment property values for a shipment.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/shipment-property-value/sale-shipment-property-value-modify.html
     *
     * @param array $fields Root object with structure: [ 'shipment' => ['id' => int, 'propertyValues' => [ ['shipmentPropsId' => int, 'value' => string], ... ] ] ]
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.shipmentpropertyvalue.modify',
        'https://apidocs.bitrix24.com/api-reference/sale/shipment-property-value/sale-shipment-property-value-modify.html',
        'Updates the shipment property values.'
    )]
    public function modify(array $fields): UpdatedShipmentPropertyValueResult
    {
        return new UpdatedShipmentPropertyValueResult(
            $this->core->call('sale.shipmentpropertyvalue.modify', [
                'fields' => $fields,
            ])
        );
    }

    /**
     * Returns shipment property value by its identifier.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/shipment-property-value/sale-shipment-property-value-get.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.shipmentpropertyvalue.get',
        'https://apidocs.bitrix24.com/api-reference/sale/shipment-property-value/sale-shipment-property-value-get.html',
        'Returns the shipment property value.'
    )]
    public function get(int $id): ShipmentPropertyValueResult
    {
        return new ShipmentPropertyValueResult(
            $this->core->call('sale.shipmentpropertyvalue.get', [
                'id' => $id,
            ])
        );
    }

    /**
     * Returns a list of shipment property values.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/shipment-property-value/sale-shipment-property-value-list.html
     *
     * @param array $select Fields to select
     * @param array $filter Filter criteria
     * @param array $order  Sort order
     * @param int   $start  Pagination start (offset), page size is 50
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.shipmentpropertyvalue.list',
        'https://apidocs.bitrix24.com/api-reference/sale/shipment-property-value/sale-shipment-property-value-list.html',
        'Returns a list of shipment property values.'
    )]
    public function list(array $select = [], array $filter = [], array $order = [], int $start = 0): ShipmentPropertyValuesResult
    {
        return new ShipmentPropertyValuesResult(
            $this->core->call('sale.shipmentpropertyvalue.list', [
                'select' => $select,
                'filter' => $filter,
                'order' => $order,
                'start' => $start,
            ])
        );
    }

    /**
     * Deletes a shipment property value.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/shipment-property-value/sale-shipment-propertyvalue-delete.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.shipmentpropertyvalue.delete',
        'https://apidocs.bitrix24.com/api-reference/sale/shipment-property-value/sale-shipment-propertyvalue-delete.html',
        'Deletes a shipment property value.'
    )]
    public function delete(int $id): DeletedItemResult
    {
        return new DeletedItemResult(
            $this->core->call('sale.shipmentpropertyvalue.delete', [
                'id' => $id,
            ])
        );
    }

    /**
     * Returns available fields for shipment property values.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/shipment-property-value/sale-shipment-property-value-get-fields.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.shipmentpropertyvalue.getFields',
        'https://apidocs.bitrix24.com/api-reference/sale/shipment-property-value/sale-shipment-property-value-get-fields.html',
        'Returns the fields and settings for shipment property values.'
    )]
    public function getFields(): ShipmentPropertyValueFieldsResult
    {
        return new ShipmentPropertyValueFieldsResult(
            $this->core->call('sale.shipmentpropertyvalue.getFields', [])
        );
    }
}
