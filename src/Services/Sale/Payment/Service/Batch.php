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
use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Services\Sale\Payment\Result\UpdatedPaymentBatchResult;
use Bitrix24\SDK\Services\Sale\Payment\Result\AddedPaymentBatchResult;
use Bitrix24\SDK\Services\Sale\Payment\Result\PaymentItemResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['sale']))]
class Batch
{
    /**
     * Batch constructor.
     */
    public function __construct(protected BatchOperationsInterface $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch add method for creating multiple payments
     *
     * @param array $payments Array of payment fields
     *
     * @return Generator<int, AddedPaymentBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.payment.add',
        'https://apidocs.bitrix24.com/api-reference/sale/payment/sale-payment-add.html',
        'Creates a new payment'
    )]
    public function add(array $payments): Generator
    {
        $fields = [];
        foreach ($payments as $payment) {
            $fields[] = [
                'fields' => $payment
            ];
        }

        foreach ($this->batch->addEntityItems('sale.payment.add', $fields) as $key => $item) {
            yield $key => new AddedPaymentBatchResult($item);
        }
    }

    /**
     * Batch update method for updating multiple payments
     *
     * Update elements in array with structure
     * element_id => [  // payment id
     *  'fields' => [] // payment fields to update
     * ]
     *
     * @param array<int, array> $paymentsData
     * @return Generator<int, UpdatedPaymentBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.payment.update',
        'https://apidocs.bitrix24.com/api-reference/sale/payment/sale-payment-update.html',
        'Updates an existing payment'
    )]
    public function update(array $paymentsData): Generator
    {
        foreach ($this->batch->updateEntityItems('sale.payment.update', $paymentsData) as $key => $item) {
            yield $key => new UpdatedPaymentBatchResult($item);
        }
    }

    /**
     * Batch list method for retrieving multiple payments
     *
     * @param array $filter Filter criteria
     * @param array $order Sort order
     * @param array $select Fields to select
     * @param int|null $limit Maximum number of items to return
     *
     * @return Generator|PaymentItemResult[]
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.payment.list',
        'https://apidocs.bitrix24.com/api-reference/sale/payment/sale-payment-list.html',
        'Retrieves a list of payments'
    )]
    public function list(array $filter = [], array $order = [], array $select = [], ?int $limit = null): Generator
    {
        foreach ($this->batch->getTraversableList(
            'sale.payment.list',
            $order,
            $filter,
            $select,
            $limit
        ) as $key => $item) {
            yield $key => new PaymentItemResult($item);
        }
    }

    /**
     * Batch delete payments
     *
     * @param int[] $paymentIds
     *
     * @return Generator<int, DeletedItemBatchResult>
     * @throws BaseException
     */
    #[ApiEndpointMetadata(
        'sale.payment.delete',
        'https://apidocs.bitrix24.com/api-reference/sale/payment/sale-payment-delete.html',
        'Batch delete payments.'
    )]
    public function delete(array $paymentIds): Generator
    {
        foreach ($this->batch->deleteEntityItems('sale.payment.delete', $paymentIds) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }
}
