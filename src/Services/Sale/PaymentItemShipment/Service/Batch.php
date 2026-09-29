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

namespace Bitrix24\SDK\Services\Sale\PaymentItemShipment\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Services\Sale\PaymentItemShipment\Result\PaymentItemShipmentAddedBatchResult;
use Bitrix24\SDK\Services\Sale\PaymentItemShipment\Result\PaymentItemShipmentItemResult;
use Bitrix24\SDK\Services\Sale\PaymentItemShipment\Result\PaymentItemShipmentUpdatedBatchResult;
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
     * Batch list method for payment item shipment bindings
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/payment-item-shipment/sale-payment-item-shipment-list.html
     *
     * @param array $select Fields to select
     * @param array $filter Filter criteria
     * @param array $order  Sort order
     * @param int|null $limit Maximum number of items to return
     *
     * @return Generator<int, PaymentItemShipmentItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.paymentitemshipment.list',
        'https://apidocs.bitrix24.com/api-reference/sale/payment-item-shipment/sale-payment-item-shipment-list.html',
        'Batch list method for payment item shipment bindings'
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

        foreach ($this->batch->getTraversableList('sale.paymentitemshipment.list', $order, $filter, $select, $limit) as $key => $value) {
            yield $key => new PaymentItemShipmentItemResult($value);
        }
    }

    /**
     * Batch adding payment item shipment bindings
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/payment-item-shipment/sale-payment-item-shipment-add.html
     *
     * @param array<int, array> $paymentItemShipments Array of payment item shipment binding fields
     *
     * @return Generator<int, PaymentItemShipmentAddedBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.paymentitemshipment.add',
        'https://apidocs.bitrix24.com/api-reference/sale/payment-item-shipment/sale-payment-item-shipment-add.html',
        'Batch adding payment item shipment bindings'
    )]
    public function add(array $paymentItemShipments): Generator
    {
        $items = [];
        foreach ($paymentItemShipments as $paymentItemShipment) {
            $items[] = ['fields' => $paymentItemShipment];
        }

        foreach ($this->batch->addEntityItems('sale.paymentitemshipment.add', $items) as $key => $item) {
            yield $key => new PaymentItemShipmentAddedBatchResult($item);
        }
    }

    /**
     * Batch update payment item shipment bindings
     *
     * Update elements in array with structure
     * element_id => [ // payment item shipment binding id
     *  // payment item shipment binding fields to update
     * ]
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/payment-item-shipment/sale-payment-item-shipment-update.html
     *
     * @param array<int, array> $paymentItemShipments keyed by payment item shipment binding id
     *
     * @return Generator<int, PaymentItemShipmentUpdatedBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.paymentitemshipment.update',
        'https://apidocs.bitrix24.com/api-reference/sale/payment-item-shipment/sale-payment-item-shipment-update.html',
        'Batch update payment item shipment bindings'
    )]
    public function update(array $paymentItemShipments): Generator
    {
        $items = [];
        foreach ($paymentItemShipments as $id => $paymentItemShipment) {
            $items[$id] = ['fields' => $paymentItemShipment];
        }

        foreach ($this->batch->updateEntityItems('sale.paymentitemshipment.update', $items) as $key => $item) {
            yield $key => new PaymentItemShipmentUpdatedBatchResult($item);
        }
    }

    /**
     * Batch delete payment item shipment bindings
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/payment-item-shipment/sale-payment-item-shipment-delete.html
     *
     * @param int[] $paymentItemShipmentId
     *
     * @return Generator<int, DeletedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.paymentitemshipment.delete',
        'https://apidocs.bitrix24.com/api-reference/sale/payment-item-shipment/sale-payment-item-shipment-delete.html',
        'Batch delete payment item shipment bindings'
    )]
    public function delete(array $paymentItemShipmentId): Generator
    {
        foreach ($this->batch->deleteEntityItems('sale.paymentitemshipment.delete', $paymentItemShipmentId) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }
}
