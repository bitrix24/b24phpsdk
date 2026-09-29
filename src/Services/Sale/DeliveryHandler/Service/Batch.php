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

namespace Bitrix24\SDK\Services\Sale\DeliveryHandler\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AddedItemBatchResult;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Core\Result\UpdatedItemBatchResult;
use Bitrix24\SDK\Services\Sale\DeliveryHandler\Result\DeliveryHandlerItemResult;
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
     * Batch list method for delivery service handlers
     *
     * The sale.delivery.handler.list method does not support pagination, all items are returned by a single call.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/delivery/handler/sale-delivery-handler-list.html
     *
     * @param int|null $limit Maximum number of items to return
     *
     * @return Generator<int, DeliveryHandlerItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.delivery.handler.list',
        'https://apidocs.bitrix24.com/api-reference/sale/delivery/handler/sale-delivery-handler-list.html',
        'Batch list method for delivery service handlers'
    )]
    public function list(?int $limit = null): Generator
    {
        $this->log->debug(
            'batchList',
            [
                'limit' => $limit,
            ]
        );

        foreach ($this->batch->getTraversableList('sale.delivery.handler.list', [], [], [], $limit) as $key => $value) {
            yield $key => new DeliveryHandlerItemResult($value);
        }
    }

    /**
     * Batch adding delivery service handlers
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/delivery/handler/sale-delivery-handler-add.html
     *
     * @param array<int, array> $handlers Array of delivery service handler fields (NAME, CODE, SORT, DESCRIPTION, SETTINGS, PROFILES)
     *
     * @return Generator<int, AddedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.delivery.handler.add',
        'https://apidocs.bitrix24.com/api-reference/sale/delivery/handler/sale-delivery-handler-add.html',
        'Batch adding delivery service handlers'
    )]
    public function add(array $handlers): Generator
    {
        foreach ($this->batch->addEntityItems('sale.delivery.handler.add', $handlers) as $key => $item) {
            yield $key => new AddedItemBatchResult($item);
        }
    }

    /**
     * Batch update delivery service handlers
     *
     * Update elements in array with structure
     * element_id => [ // delivery service handler id
     *  // delivery service handler fields to update
     * ]
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/delivery/handler/sale-delivery-handler-update.html
     *
     * @param array<int, array> $handlers keyed by delivery service handler id
     *
     * @return Generator<int, UpdatedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.delivery.handler.update',
        'https://apidocs.bitrix24.com/api-reference/sale/delivery/handler/sale-delivery-handler-update.html',
        'Batch update delivery service handlers'
    )]
    public function update(array $handlers): Generator
    {
        foreach ($this->batch->updateEntityItems('sale.delivery.handler.update', $handlers) as $key => $item) {
            yield $key => new UpdatedItemBatchResult($item);
        }
    }

    /**
     * Batch delete delivery service handlers
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/delivery/handler/sale-delivery-handler-delete.html
     *
     * @param int[] $handlerId
     *
     * @return Generator<int, DeletedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.delivery.handler.delete',
        'https://apidocs.bitrix24.com/api-reference/sale/delivery/handler/sale-delivery-handler-delete.html',
        'Batch delete delivery service handlers'
    )]
    public function delete(array $handlerId): Generator
    {
        foreach ($this->batch->deleteEntityItems('sale.delivery.handler.delete', $handlerId) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }
}
