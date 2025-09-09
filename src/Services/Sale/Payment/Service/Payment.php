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

namespace Bitrix24\SDK\Services\Sale\Payment\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Sale\Payment\Result\PaymentResult;
use Bitrix24\SDK\Services\Sale\Payment\Result\PaymentsResult;
use Bitrix24\SDK\Services\Sale\Payment\Result\PaymentFieldsResult;
use Bitrix24\SDK\Services\Sale\Payment\Result\PaymentAddedResult;
use Bitrix24\SDK\Services\Sale\Payment\Result\PaymentUpdatedResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['sale']))]
class Payment extends AbstractService
{
    public function __construct(CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Adds a payment.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/payment/sale-payment-add.html
     *
     * @param array $fields Field values for creating a payment
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.payment.add',
        'https://apidocs.bitrix24.com/api-reference/sale/payment/sale-payment-add.html',
        'Creates a new payment.'
    )]
    public function add(array $fields): PaymentAddedResult
    {
        return new PaymentAddedResult(
            $this->core->call('sale.payment.add', [
                'fields' => $fields
            ])
        );
    }

    /**
     * Updates the fields of a payment.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/payment/sale-payment-update.html
     *
     * @param int   $id     Payment identifier
     * @param array $fields Field values for update
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.payment.update',
        'https://apidocs.bitrix24.com/api-reference/sale/payment/sale-payment-update.html',
        'Updates an existing payment.'
    )]
    public function update(int $id, array $fields): PaymentUpdatedResult
    {
        return new PaymentUpdatedResult(
            $this->core->call('sale.payment.update', [
                'id' => $id,
                'fields' => $fields
            ])
        );
    }

    /**
     * Returns a payment by its identifier.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/payment/sale-payment-get.html
     *
     * @param int $id Payment identifier
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.payment.get',
        'https://apidocs.bitrix24.com/api-reference/sale/payment/sale-payment-get.html',
        'Retrieves information about a payment.'
    )]
    public function get(int $id): PaymentResult
    {
        return new PaymentResult(
            $this->core->call('sale.payment.get', [
                'id' => $id
            ])
        );
    }

    /**
     * Returns a list of payments.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/payment/sale-payment-list.html
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
        'sale.payment.list',
        'https://apidocs.bitrix24.com/api-reference/sale/payment/sale-payment-list.html',
        'Retrieves a list of payments.'
    )]
    public function list(array $select = [], array $filter = [], array $order = [], int $start = 0): PaymentsResult
    {
        return new PaymentsResult(
            $this->core->call('sale.payment.list', [
                'select' => $select,
                'filter' => $filter,
                'order' => $order,
                'start' => $start
            ])
        );
    }

    /**
     * Deletes a payment.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/payment/sale-payment-delete.html
     *
     * @param int $id Payment identifier
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.payment.delete',
        'https://apidocs.bitrix24.com/api-reference/sale/payment/sale-payment-delete.html',
        'Deletes a payment.'
    )]
    public function delete(int $id): DeletedItemResult
    {
        return new DeletedItemResult(
            $this->core->call('sale.payment.delete', [
                'id' => $id
            ])
        );
    }

    /**
     * Returns the fields and settings for payments.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/payment/sale-payment-get-fields.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.payment.getFields',
        'https://apidocs.bitrix24.com/api-reference/sale/payment/sale-payment-get-fields.html',
        'Retrieves the description of payment fields.'
    )]
    public function getFields(): PaymentFieldsResult
    {
        return new PaymentFieldsResult(
            $this->core->call('sale.payment.getFields', [])
        );
    }
}
