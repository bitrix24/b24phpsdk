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

namespace Bitrix24\SDK\Services\Paysystem\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\AddedItemResult;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Paysystem\Result\PaymentResult;
use Bitrix24\SDK\Services\Paysystem\Result\PaysystemsResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['pay_system']))]
class Paysystem extends AbstractService
{
    /**
     * Paysystem constructor.
     */
    public function __construct(public Batch $batch, CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Adds a payment system.
     *
     * @link https://apidocs.bitrix24.com/api-reference/pay-system/sale-pay-system-add.html
     *
     * @param array $fields Field values for creating a payment system
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.paysystem.add',
        'https://apidocs.bitrix24.com/api-reference/pay-system/sale-pay-system-add.html',
        'Adds a payment system.'
    )]
    public function add(array $fields): AddedItemResult
    {
        return new AddedItemResult(
            $this->core->call('sale.paysystem.add', $fields)
        );
    }

    /**
     * Modifies a payment system.
     *
     * @link https://apidocs.bitrix24.com/api-reference/pay-system/sale-pay-system-update.html
     *
     * @param int   $id     Payment system identifier
     * @param array $fields Field values for update
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.paysystem.update',
        'https://apidocs.bitrix24.com/api-reference/pay-system/sale-pay-system-update.html',
        'Modifies a payment system.'
    )]
    public function update(int $id, array $fields): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call('sale.paysystem.update', [
                'ID' => $id,
                'FIELDS' => $fields,
            ])
        );
    }    /**
     * Returns a list of payment systems.
     *
     * @link https://apidocs.bitrix24.com/api-reference/pay-system/sale-pay-system-list.html
     *
     * @param array $select Fields to select
     * @param array $filter Filter criteria
     * @param array $order  Sort order
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.paysystem.list',
        'https://apidocs.bitrix24.com/api-reference/pay-system/sale-pay-system-list.html',
        'Returns a list of payment systems.'
    )]
    public function list(array $select = [], array $filter = [], array $order = []): PaysystemsResult
    {
        return new PaysystemsResult(
            $this->core->call('sale.paysystem.list', [
                'SELECT' => $select,
                'FILTER' => $filter,
                'ORDER' => $order,
            ])
        );
    }

    /**
     * Deletes a payment system.
     *
     * @link https://apidocs.bitrix24.com/api-reference/pay-system/sale-pay-system-delete.html
     *
     * @param int $id Payment system identifier
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.paysystem.delete',
        'https://apidocs.bitrix24.com/api-reference/pay-system/sale-pay-system-delete.html',
        'Deletes a payment system.'
    )]
    public function delete(int $id): DeletedItemResult
    {
        return new DeletedItemResult(
            $this->core->call('sale.paysystem.delete', [
                'ID' => $id,
            ])
        );
    }

    /**
     * Pay for an order through a specific payment system.
     *
     * @link https://apidocs.bitrix24.com/api-reference/pay-system/sale-pay-system-pay-payment.html
     *
     * @param int $paymentId   Payment identifier
     * @param int $paySystemId Payment system identifier
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.paysystem.pay.payment',
        'https://apidocs.bitrix24.com/api-reference/pay-system/sale-pay-system-pay-payment.html',
        'Pay for an order through a specific payment system.'
    )]
    public function payPayment(int $paymentId, int $paySystemId): PaymentResult
    {
        return new PaymentResult(
            $this->core->call('sale.paysystem.pay.payment', [
                'PAYMENT_ID' => $paymentId,
                'PAY_SYSTEM_ID' => $paySystemId,
            ])
        );
    }

    /**
     * Pay an invoice through a specific payment system.
     *
     * @link https://apidocs.bitrix24.com/api-reference/pay-system/sale-pay-system-pay-invoice.html
     *
     * @param int         $invoiceId        Identifier of the old version invoice
     * @param int|null    $paySystemId      Identifier of the payment system
     * @param string|null $bxRestHandler    Symbolic identifier of the payment system's REST handler
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.paysystem.pay.invoice',
        'https://apidocs.bitrix24.com/api-reference/pay-system/sale-pay-system-pay-invoice.html',
        'Pay an invoice through a specific payment system.'
    )]
    public function payInvoice(int $invoiceId, ?int $paySystemId = null, ?string $bxRestHandler = null): PaymentResult
    {
        $params = ['INVOICE_ID' => $invoiceId];
        
        if ($paySystemId !== null) {
            $params['PAY_SYSTEM_ID'] = $paySystemId;
        }
        
        if ($bxRestHandler !== null) {
            $params['BX_REST_HANDLER'] = $bxRestHandler;
        }

        return new PaymentResult(
            $this->core->call('sale.paysystem.pay.invoice', $params)
        );
    }
}
