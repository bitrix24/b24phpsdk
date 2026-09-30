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

namespace Bitrix24\SDK\Services\Sale\Delivery\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Core\Result\UpdatedItemBatchResult;
use Bitrix24\SDK\Services\Sale\Delivery\Result\DeliveryAddBatchResult;
use Bitrix24\SDK\Services\Sale\Delivery\Result\DeliveryItemResult;
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
     * Batch list method for delivery services
     *
     * The sale.delivery.getlist method does not support pagination, all items are returned by a single call.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/delivery/delivery/sale-delivery-get-list.html
     *
     * @param array $select Fields to select
     * @param array $filter Filter criteria
     * @param array $order  Sort order
     * @param int|null $limit Maximum number of items to return
     *
     * @return Generator<int, DeliveryItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.delivery.getlist',
        'https://apidocs.bitrix24.com/api-reference/sale/delivery/delivery/sale-delivery-get-list.html',
        'Batch list method for delivery services'
    )]
    public function getlist(array $select = [], array $filter = [], array $order = [], ?int $limit = null): Generator
    {
        $this->log->debug(
            'batchGetlist',
            [
                'select' => $select,
                'filter' => $filter,
                'order' => $order,
                'limit' => $limit,
            ]
        );

        foreach ($this->batch->getTraversableList('sale.delivery.getlist', $order, $filter, $select, $limit) as $key => $value) {
            yield $key => new DeliveryItemResult($value);
        }
    }

    /**
     * Batch adding delivery services
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/delivery/delivery/sale-delivery-add.html
     *
     * @param array<int, array> $deliveries Array of delivery service fields (REST_CODE, NAME, CURRENCY, ...)
     *
     * @return Generator<int, DeliveryAddBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.delivery.add',
        'https://apidocs.bitrix24.com/api-reference/sale/delivery/delivery/sale-delivery-add.html',
        'Batch adding delivery services'
    )]
    public function add(array $deliveries): Generator
    {
        foreach ($this->batch->addEntityItems('sale.delivery.add', $deliveries) as $key => $item) {
            yield $key => new DeliveryAddBatchResult($item);
        }
    }

    /**
     * Batch update delivery services
     *
     * Update elements in array with structure
     * element_id => [ // delivery service id
     *  // delivery service fields to update (NAME, CURRENCY, DESCRIPTION, SORT, ACTIVE, LOGOTYPE)
     * ]
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/delivery/delivery/sale-delivery-update.html
     *
     * @param array<int, array> $deliveries keyed by delivery service id
     *
     * @return Generator<int, UpdatedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.delivery.update',
        'https://apidocs.bitrix24.com/api-reference/sale/delivery/delivery/sale-delivery-update.html',
        'Batch update delivery services'
    )]
    public function update(array $deliveries): Generator
    {
        foreach ($this->batch->updateEntityItems('sale.delivery.update', $deliveries) as $key => $item) {
            yield $key => new UpdatedItemBatchResult($item);
        }
    }

    /**
     * Batch update delivery services settings
     *
     * Update elements in array with structure
     * element_id => [ // delivery service id
     *  ['CODE' => 'SETTING_CODE', 'VALUE' => 'value'],
     *  ...
     * ]
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/delivery/delivery/sale-delivery-config-update.html
     *
     * @param array<int, array<int, array{CODE: string, VALUE: mixed}>> $configs keyed by delivery service id
     *
     * @return Generator<int, UpdatedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.delivery.config.update',
        'https://apidocs.bitrix24.com/api-reference/sale/delivery/delivery/sale-delivery-config-update.html',
        'Batch update delivery services settings'
    )]
    public function configUpdate(array $configs): Generator
    {
        $items = [];
        foreach ($configs as $id => $config) {
            $items[$id] = ['CONFIG' => $config];
        }

        foreach ($this->batch->updateEntityItems('sale.delivery.config.update', $items) as $key => $item) {
            yield $key => new UpdatedItemBatchResult($item);
        }
    }

    /**
     * Batch delete delivery services
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/delivery/delivery/sale-delivery-delete.html
     *
     * @param int[] $deliveryId
     *
     * @return Generator<int, DeletedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.delivery.delete',
        'https://apidocs.bitrix24.com/api-reference/sale/delivery/delivery/sale-delivery-delete.html',
        'Batch delete delivery services'
    )]
    public function delete(array $deliveryId): Generator
    {
        foreach ($this->batch->deleteEntityItems('sale.delivery.delete', $deliveryId) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }
}
