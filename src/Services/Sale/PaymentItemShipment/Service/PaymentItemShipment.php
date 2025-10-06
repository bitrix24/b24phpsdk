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

namespace Bitrix24\SDK\Services\Sale\PaymentItemShipment\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Sale\PaymentItemShipment\Result\PaymentItemShipmentResult;
use Bitrix24\SDK\Services\Sale\PaymentItemShipment\Result\PaymentItemShipmentsResult;
use Bitrix24\SDK\Services\Sale\PaymentItemShipment\Result\PaymentItemShipmentFieldsResult;
use Bitrix24\SDK\Services\Sale\PaymentItemShipment\Result\PaymentItemShipmentAddedResult;
use Bitrix24\SDK\Services\Sale\PaymentItemShipment\Result\PaymentItemShipmentUpdatedResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['sale']))]
class PaymentItemShipment extends AbstractService
{
    public function __construct(CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Adds a binding of a payment to a shipment.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/payment-item-shipment/sale-payment-item-shipment-add.html
     *
     * @param array $fields Field values for creating a binding of a payment to a shipment
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.paymentitemshipment.add',
        'https://apidocs.bitrix24.com/api-reference/sale/payment-item-shipment/sale-payment-item-shipment-add.html',
        'Creates a new binding of a payment to a shipment.'
    )]
    public function add(array $fields): PaymentItemShipmentAddedResult
    {
        return new PaymentItemShipmentAddedResult(
            $this->core->call('sale.paymentitemshipment.add', [
                'fields' => $fields
            ])
        );
    }

    /**
     * Updates the binding of a payment to a shipment.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/payment-item-shipment/sale-payment-item-shipment-update.html
     *
     * @param int   $id     Identifier of the binding of the payment to the shipment
     * @param array $fields Field values for updating the binding of the payment to the shipment
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.paymentitemshipment.update',
        'https://apidocs.bitrix24.com/api-reference/sale/payment-item-shipment/sale-payment-item-shipment-update.html',
        'Updates an existing binding of a payment to a shipment.'
    )]
    public function update(int $id, array $fields): PaymentItemShipmentUpdatedResult
    {
        return new PaymentItemShipmentUpdatedResult(
            $this->core->call('sale.paymentitemshipment.update', [
                'id' => $id,
                'fields' => $fields
            ])
        );
    }

    /**
     * Returns the values of all fields for the payment binding to shipment.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/payment-item-shipment/sale-payment-item-shipment-get.html
     *
     * @param int $id Identifier of the payment binding to shipment
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.paymentitemshipment.get',
        'https://apidocs.bitrix24.com/api-reference/sale/payment-item-shipment/sale-payment-item-shipment-get.html',
        'Retrieves information about a payment binding to shipment.'
    )]
    public function get(int $id): PaymentItemShipmentResult
    {
        return new PaymentItemShipmentResult(
            $this->core->call('sale.paymentitemshipment.get', [
                'id' => $id
            ])
        );
    }

    /**
     * Returns a list of bindings of payments to shipments.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/payment-item-shipment/sale-payment-item-shipment-list.html
     *
     * @param array $filter Filter criteria
     * @param array $order Sort order
     * @param array $select Fields to select
     * @param int   $start Pagination start (offset)
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.paymentitemshipment.list',
        'https://apidocs.bitrix24.com/api-reference/sale/payment-item-shipment/sale-payment-item-shipment-list.html',
        'Retrieves a list of payment bindings to shipments.'
    )]
    public function list(array $select = [], array $filter = [], array $order = [], int $start = 0): PaymentItemShipmentsResult
    {
        return new PaymentItemShipmentsResult(
            $this->core->call('sale.paymentitemshipment.list', [
                'select' => $select,
                'filter' => $filter,
                'order' => $order,
                'start' => $start
            ])
        );
    }

    /**
     * Deletes the binding of a payment to a shipment.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/payment-item-shipment/sale-payment-item-shipment-delete.html
     *
     * @param int $id Identifier of the binding of the payment to the shipment
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.paymentitemshipment.delete',
        'https://apidocs.bitrix24.com/api-reference/sale/payment-item-shipment/sale-payment-item-shipment-delete.html',
        'Deletes a binding of a payment to a shipment.'
    )]
    public function delete(int $id): DeletedItemResult
    {
        return new DeletedItemResult(
            $this->core->call('sale.paymentitemshipment.delete', [
                'id' => $id
            ])
        );
    }

    /**
     * Returns the available fields for payment item shipment bindings.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/payment-item-shipment/sale-payment-item-shipment-get-fields.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.paymentitemshipment.getfields',
        'https://apidocs.bitrix24.com/api-reference/sale/payment-item-shipment/sale-payment-item-shipment-get-fields.html',
        'Retrieves the description of payment item shipment binding fields.'
    )]
    public function getFields(): PaymentItemShipmentFieldsResult
    {
        return new PaymentItemShipmentFieldsResult(
            $this->core->call('sale.paymentitemshipment.getfields', [])
        );
    }
}
