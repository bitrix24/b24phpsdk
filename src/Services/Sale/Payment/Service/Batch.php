<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <algonexys@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Sale\Payment\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Services\Sale\Payment\Result\PaymentAddedBatchResult;
use Bitrix24\SDK\Services\Sale\Payment\Result\PaymentItemResult;
use Bitrix24\SDK\Services\Sale\Payment\Result\PaymentUpdatedBatchResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['sale']))]
class Batch
{
    /**
     * Batch constructor
     */
    public function __construct(protected BatchOperationsInterface $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch list method for payments
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/payment/sale-payment-list.html
     *
     * @param array $select Fields to select
     * @param array $filter Filter criteria
     * @param array $order  Sort order
     * @param int|null $limit Maximum number of items to return
     *
     * @return Generator<int, PaymentItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.payment.list',
        'https://apidocs.bitrix24.com/api-reference/sale/payment/sale-payment-list.html',
        'Batch list method for payments'
    )]
    public function list(array $select = [], array $filter = [], array $order = [], ?int $limit = null): Generator
    {
        $this->log->debug(
            'batchList',
            [
                'select' => $select,
                'filter' => $filter,
                'order' => $order,
                'limit' => $limit,
            ]
        );

        foreach ($this->batch->getTraversableList('sale.payment.list', $order, $filter, $select, $limit) as $key => $value) {
            yield $key => new PaymentItemResult($value);
        }
    }

    /**
     * Batch adding payments
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/payment/sale-payment-add.html
     *
     * @param array<int, array> $payments Array of payment fields
     *
     * @return Generator<int, PaymentAddedBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.payment.add',
        'https://apidocs.bitrix24.com/api-reference/sale/payment/sale-payment-add.html',
        'Batch adding payments'
    )]
    public function add(array $payments): Generator
    {
        $items = [];
        foreach ($payments as $payment) {
            $items[] = ['fields' => $payment];
        }

        foreach ($this->batch->addEntityItems('sale.payment.add', $items) as $key => $item) {
            yield $key => new PaymentAddedBatchResult($item);
        }
    }

    /**
     * Batch update payments
     *
     * Update elements in array with structure
     * element_id => [ // payment id
     *  // payment fields to update
     * ]
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/payment/sale-payment-update.html
     *
     * @param array<int, array> $payments keyed by payment id
     *
     * @return Generator<int, PaymentUpdatedBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.payment.update',
        'https://apidocs.bitrix24.com/api-reference/sale/payment/sale-payment-update.html',
        'Batch update payments'
    )]
    public function update(array $payments): Generator
    {
        $items = [];
        foreach ($payments as $id => $payment) {
            $items[$id] = ['fields' => $payment];
        }

        foreach ($this->batch->updateEntityItems('sale.payment.update', $items) as $key => $item) {
            yield $key => new PaymentUpdatedBatchResult($item);
        }
    }

    /**
     * Batch delete payments
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/payment/sale-payment-delete.html
     *
     * @param int[] $paymentId
     *
     * @return Generator<int, DeletedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.payment.delete',
        'https://apidocs.bitrix24.com/api-reference/sale/payment/sale-payment-delete.html',
        'Batch delete payments'
    )]
    public function delete(array $paymentId): Generator
    {
        foreach ($this->batch->deleteEntityItems('sale.payment.delete', $paymentId) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }
}
