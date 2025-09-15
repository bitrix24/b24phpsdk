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

namespace Bitrix24\SDK\Services\Sale\PaymentItemBasket\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Sale\PaymentItemBasket\Result\PaymentItemBasketResult;
use Bitrix24\SDK\Services\Sale\PaymentItemBasket\Result\PaymentItemBasketsResult;
use Bitrix24\SDK\Services\Sale\PaymentItemBasket\Result\PaymentItemBasketFieldsResult;
use Bitrix24\SDK\Services\Sale\PaymentItemBasket\Result\PaymentItemBasketAddedResult;
use Bitrix24\SDK\Services\Sale\PaymentItemBasket\Result\PaymentItemBasketUpdatedResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['sale']))]
class PaymentItemBasket extends AbstractService
{
    public function __construct(CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Adds a binding of a basket item to a payment.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/payment-item-basket/sale-payment-item-basket-add.html
     *
     * @param array $fields Field values for creating a binding of a basket item to a payment
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.paymentitembasket.add',
        'https://apidocs.bitrix24.com/api-reference/sale/payment-item-basket/sale-payment-item-basket-add.html',
        'Creates a new binding of a basket item to a payment.'
    )]
    public function add(array $fields): PaymentItemBasketAddedResult
    {
        return new PaymentItemBasketAddedResult(
            $this->core->call('sale.paymentitembasket.add', [
                'fields' => $fields
            ])
        );
    }

    /**
     * Updates the binding of a basket item to a payment.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/payment-item-basket/sale-payment-item-basket-update.html
     *
     * @param int   $id     Identifier of the binding of the basket item to the payment
     * @param array $fields Field values for updating the binding of the basket item to the payment
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.paymentitembasket.update',
        'https://apidocs.bitrix24.com/api-reference/sale/payment-item-basket/sale-payment-item-basket-update.html',
        'Updates an existing binding of a basket item to a payment.'
    )]
    public function update(int $id, array $fields): PaymentItemBasketUpdatedResult
    {
        return new PaymentItemBasketUpdatedResult(
            $this->core->call('sale.paymentitembasket.update', [
                'id' => $id,
                'fields' => $fields
            ])
        );
    }

    /**
     * Returns the values of all fields for the basket item binding to payment.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/payment-item-basket/sale-payment-item-basket-get.html
     *
     * @param int $id Identifier of the basket item binding to payment
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.paymentitembasket.get',
        'https://apidocs.bitrix24.com/api-reference/sale/payment-item-basket/sale-payment-item-basket-get.html',
        'Retrieves information about a basket item binding to payment.'
    )]
    public function get(int $id): PaymentItemBasketResult
    {
        return new PaymentItemBasketResult(
            $this->core->call('sale.paymentitembasket.get', [
                'id' => $id
            ])
        );
    }

    /**
     * Returns a list of bindings of basket items to payments.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/payment-item-basket/sale-payment-item-basket-list.html
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
        'sale.paymentitembasket.list',
        'https://apidocs.bitrix24.com/api-reference/sale/payment-item-basket/sale-payment-item-basket-list.html',
        'Retrieves a list of basket item bindings to payments.'
    )]
    public function list(array $select = [], array $filter = [], array $order = [], int $start = 0): PaymentItemBasketsResult
    {
        return new PaymentItemBasketsResult(
            $this->core->call('sale.paymentitembasket.list', [
                'select' => $select,
                'filter' => $filter,
                'order' => $order,
                'start' => $start
            ])
        );
    }

    /**
     * Deletes the binding of a basket item to a payment.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/payment-item-basket/sale-payment-item-basket-delete.html
     *
     * @param int $id Identifier of the binding of the basket item to the payment
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.paymentitembasket.delete',
        'https://apidocs.bitrix24.com/api-reference/sale/payment-item-basket/sale-payment-item-basket-delete.html',
        'Deletes a binding of a basket item to a payment.'
    )]
    public function delete(int $id): DeletedItemResult
    {
        return new DeletedItemResult(
            $this->core->call('sale.paymentitembasket.delete', [
                'id' => $id
            ])
        );
    }

    /**
     * Returns the available fields for payment item basket bindings.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/payment-item-basket/sale-payment-item-basket-get-fields.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.paymentitembasket.getfields',
        'https://apidocs.bitrix24.com/api-reference/sale/payment-item-basket/sale-payment-item-basket-get-fields.html',
        'Retrieves the description of payment item basket binding fields.'
    )]
    public function getFields(): PaymentItemBasketFieldsResult
    {
        return new PaymentItemBasketFieldsResult(
            $this->core->call('sale.paymentitembasket.getfields', [])
        );
    }
}
