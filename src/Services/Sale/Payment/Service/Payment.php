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

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Services\Sale\Payment\Result\PaymentResult;
use Bitrix24\SDK\Services\Sale\Payment\Result\PaymentsResult;
use Bitrix24\SDK\Services\Sale\Payment\Result\PaymentFieldsResult;
use Bitrix24\SDK\Services\Sale\Payment\Result\PaymentAddedResult;
use Bitrix24\SDK\Services\Sale\Payment\Result\PaymentUpdatedResult;
use Psr\Log\LoggerInterface;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Attributes\ApiEndpointMetadata;

#[ApiServiceMetadata(new \Bitrix24\SDK\Core\Credentials\Scope(['sale']))]
class Payment extends \Bitrix24\SDK\Services\AbstractService
{
    public function __construct(public Batch $batch, CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    #[ApiEndpointMetadata(
        'sale.payment.add',
        'https://apidocs.bitrix24.com/api-reference/sale/payment/sale-payment-add.html',
        'Creates a new payment.'
    )]
    public function add(array $fields): PaymentAddedResult
    {
        $response = $this->core->call('sale.payment.add', [
            'fields' => $fields
        ]);
        return new PaymentAddedResult($response);
    }

    #[ApiEndpointMetadata(
        'sale.payment.update',
        'https://apidocs.bitrix24.com/api-reference/sale/payment/sale-payment-update.html',
        'Updates an existing payment.'
    )]
    public function update(int $id, array $fields): PaymentUpdatedResult
    {
        $response = $this->core->call('sale.payment.update', [
            'id' => $id,
            'fields' => $fields
        ]);
        return new PaymentUpdatedResult($response);
    }

    #[ApiEndpointMetadata(
        'sale.payment.get',
        'https://apidocs.bitrix24.com/api-reference/sale/payment/sale-payment-get.html',
        'Retrieves information about a payment.'
    )]
    public function get(int $id): PaymentResult
    {
        $response = $this->core->call('sale.payment.get', [
            'id' => $id
        ]);
        return new PaymentResult($response);
    }

    #[ApiEndpointMetadata(
        'sale.payment.list',
        'https://apidocs.bitrix24.com/api-reference/sale/payment/sale-payment-list.html',
        'Retrieves a list of payments.'
    )]
    public function list(array $filter = [], array $order = [], array $select = [], int $start = 0): PaymentsResult
    {
        $response = $this->core->call('sale.payment.list', [
            'filter' => $filter,
            'order' => $order,
            'select' => $select,
            'start' => $start
        ]);
        return new PaymentsResult($response);
    }

    #[ApiEndpointMetadata(
        'sale.payment.delete',
        'https://apidocs.bitrix24.com/api-reference/sale/payment/sale-payment-delete.html',
        'Deletes a payment.'
    )]
    public function delete(int $id): DeletedItemResult
    {
        $response = $this->core->call('sale.payment.delete', [
            'id' => $id
        ]);
        return new DeletedItemResult($response);
    }

    #[ApiEndpointMetadata(
        'sale.payment.getFields',
        'https://apidocs.bitrix24.com/api-reference/sale/payment/sale-payment-get-fields.html',
        'Retrieves the description of payment fields.'
    )]
    public function getFields(): PaymentFieldsResult
    {
        $response = $this->core->call('sale.payment.getFields', []);
        return new PaymentFieldsResult($response);
    }
}
