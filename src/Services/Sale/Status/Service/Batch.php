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

namespace Bitrix24\SDK\Services\Sale\Status\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Services\Sale\Status;
use Bitrix24\SDK\Services\Sale\Status\Result\StatusAddBatchResult;
use Bitrix24\SDK\Services\Sale\Status\Result\StatusItemResult;
use Bitrix24\SDK\Services\Sale\Status\Result\StatusUpdateBatchResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['sale']))]
class Batch
{
    /**
     * Batch constructor
     */
    public function __construct(protected Status\Batch $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch list method for order and delivery statuses
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/status/sale-status-list.html
     *
     * @param array $select Fields to select
     * @param array $filter Filter criteria
     * @param array $order  Sort order
     * @param int|null $limit Maximum number of items to return
     *
     * @return Generator<int, StatusItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.status.list',
        'https://apidocs.bitrix24.com/api-reference/sale/status/sale-status-list.html',
        'Batch list method for order and delivery statuses'
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

        foreach ($this->batch->getTraversableListWithCount('sale.status.list', $order, $filter, $select, $limit) as $key => $value) {
            yield $key => new StatusItemResult($value);
        }
    }

    /**
     * Batch adding order and delivery statuses
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/status/sale-status-add.html
     *
     * @param array<int, array> $statuses Array of status fields
     *
     * @return Generator<int, StatusAddBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.status.add',
        'https://apidocs.bitrix24.com/api-reference/sale/status/sale-status-add.html',
        'Batch adding order and delivery statuses'
    )]
    public function add(array $statuses): Generator
    {
        $items = [];
        foreach ($statuses as $status) {
            $items[] = ['fields' => $status];
        }

        foreach ($this->batch->addEntityItems('sale.status.add', $items) as $key => $item) {
            yield $key => new StatusAddBatchResult($item);
        }
    }

    /**
     * Batch update order and delivery statuses
     *
     * Update elements in array with structure
     * status_id => [ // status string identifier
     *  // status fields to update
     * ]
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/status/sale-status-update.html
     *
     * @param array<string, array> $statuses keyed by status id
     *
     * @return Generator<int, StatusUpdateBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.status.update',
        'https://apidocs.bitrix24.com/api-reference/sale/status/sale-status-update.html',
        'Batch update order and delivery statuses'
    )]
    public function update(array $statuses): Generator
    {
        $items = [];
        foreach ($statuses as $id => $status) {
            $items[$id] = ['fields' => $status];
        }

        foreach ($this->batch->updateEntityItems('sale.status.update', $items) as $key => $item) {
            yield $key => new StatusUpdateBatchResult($item);
        }
    }

    /**
     * Batch delete order and delivery statuses
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/status/sale-status-delete.html
     *
     * @param string[] $statusId
     *
     * @return Generator<int, DeletedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.status.delete',
        'https://apidocs.bitrix24.com/api-reference/sale/status/sale-status-delete.html',
        'Batch delete order and delivery statuses'
    )]
    public function delete(array $statusId): Generator
    {
        foreach ($this->batch->deleteEntityItems('sale.status.delete', $statusId) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }
}
