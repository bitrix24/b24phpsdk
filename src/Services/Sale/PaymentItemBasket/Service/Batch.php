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

namespace Bitrix24\SDK\Services\Sale\PaymentItemBasket\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Services\Sale\PaymentItemBasket\Result\PaymentItemBasketAddedBatchResult;
use Bitrix24\SDK\Services\Sale\PaymentItemBasket\Result\PaymentItemBasketItemResult;
use Bitrix24\SDK\Services\Sale\PaymentItemBasket\Result\PaymentItemBasketUpdatedBatchResult;
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
     * Batch list method for payment item basket bindings
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/payment-item-basket/sale-payment-item-basket-list.html
     *
     * @param array $select Fields to select
     * @param array $filter Filter criteria
     * @param array $order  Sort order
     * @param int|null $limit Maximum number of items to return
     *
     * @return Generator<int, PaymentItemBasketItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.paymentitembasket.list',
        'https://apidocs.bitrix24.com/api-reference/sale/payment-item-basket/sale-payment-item-basket-list.html',
        'Batch list method for payment item basket bindings'
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

        foreach ($this->batch->getTraversableList('sale.paymentitembasket.list', $order, $filter, $select, $limit) as $key => $value) {
            yield $key => new PaymentItemBasketItemResult($value);
        }
    }

    /**
     * Batch adding payment item basket bindings
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/payment-item-basket/sale-payment-item-basket-add.html
     *
     * @param array<int, array> $paymentItemBaskets Array of payment item basket binding fields
     *
     * @return Generator<int, PaymentItemBasketAddedBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.paymentitembasket.add',
        'https://apidocs.bitrix24.com/api-reference/sale/payment-item-basket/sale-payment-item-basket-add.html',
        'Batch adding payment item basket bindings'
    )]
    public function add(array $paymentItemBaskets): Generator
    {
        $items = [];
        foreach ($paymentItemBaskets as $paymentItemBasket) {
            $items[] = ['fields' => $paymentItemBasket];
        }

        foreach ($this->batch->addEntityItems('sale.paymentitembasket.add', $items) as $key => $item) {
            yield $key => new PaymentItemBasketAddedBatchResult($item);
        }
    }

    /**
     * Batch update payment item basket bindings
     *
     * Update elements in array with structure
     * element_id => [ // payment item basket binding id
     *  // payment item basket binding fields to update
     * ]
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/payment-item-basket/sale-payment-item-basket-update.html
     *
     * @param array<int, array> $paymentItemBaskets keyed by payment item basket binding id
     *
     * @return Generator<int, PaymentItemBasketUpdatedBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.paymentitembasket.update',
        'https://apidocs.bitrix24.com/api-reference/sale/payment-item-basket/sale-payment-item-basket-update.html',
        'Batch update payment item basket bindings'
    )]
    public function update(array $paymentItemBaskets): Generator
    {
        $items = [];
        foreach ($paymentItemBaskets as $id => $paymentItemBasket) {
            $items[$id] = ['fields' => $paymentItemBasket];
        }

        foreach ($this->batch->updateEntityItems('sale.paymentitembasket.update', $items) as $key => $item) {
            yield $key => new PaymentItemBasketUpdatedBatchResult($item);
        }
    }

    /**
     * Batch delete payment item basket bindings
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/payment-item-basket/sale-payment-item-basket-delete.html
     *
     * @param int[] $paymentItemBasketId
     *
     * @return Generator<int, DeletedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.paymentitembasket.delete',
        'https://apidocs.bitrix24.com/api-reference/sale/payment-item-basket/sale-payment-item-basket-delete.html',
        'Batch delete payment item basket bindings'
    )]
    public function delete(array $paymentItemBasketId): Generator
    {
        foreach ($this->batch->deleteEntityItems('sale.paymentitembasket.delete', $paymentItemBasketId) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }
}
