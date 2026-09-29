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

namespace Bitrix24\SDK\Services\Sale\Cashbox\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AddedItemBatchResult;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Core\Result\UpdatedItemBatchResult;
use Bitrix24\SDK\Services\Sale\Cashbox\Result\CashboxItemResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['sale', 'cashbox']))]
class Batch
{
    /**
     * Batch constructor
     */
    public function __construct(protected BatchOperationsInterface $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch list method for cash registers
     *
     * The sale.cashbox.list method does not support pagination, all items are returned by a single call.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/cashbox/sale-cashbox-list.html
     *
     * @param array|null $select Fields to select
     * @param array|null $filter Filter criteria
     * @param array|null $order  Sort order
     * @param int|null $limit Maximum number of items to return
     *
     * @return Generator<int, CashboxItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.cashbox.list',
        'https://apidocs.bitrix24.com/api-reference/sale/cashbox/sale-cashbox-list.html',
        'Batch list method for cash registers'
    )]
    public function list(?array $select = null, ?array $filter = null, ?array $order = null, ?int $limit = null): Generator
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

        foreach ($this->batch->getTraversableList('sale.cashbox.list', $order, $filter, $select, $limit) as $key => $value) {
            yield $key => new CashboxItemResult($value);
        }
    }

    /**
     * Batch adding cash registers
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/cashbox/sale-cashbox-add.html
     *
     * @param array<int, array> $cashboxes Array of cash register fields (NAME, REST_CODE, EMAIL, OFD, NUMBER_KKM, ...)
     *
     * @return Generator<int, AddedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.cashbox.add',
        'https://apidocs.bitrix24.com/api-reference/sale/cashbox/sale-cashbox-add.html',
        'Batch adding cash registers'
    )]
    public function add(array $cashboxes): Generator
    {
        foreach ($this->batch->addEntityItems('sale.cashbox.add', $cashboxes) as $key => $item) {
            yield $key => new AddedItemBatchResult($item);
        }
    }

    /**
     * Batch update cash registers
     *
     * Update elements in array with structure
     * element_id => [ // cash register id
     *  // cash register fields to update
     * ]
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/cashbox/sale-cashbox-update.html
     *
     * @param array<int, array> $cashboxes keyed by cash register id
     *
     * @return Generator<int, UpdatedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.cashbox.update',
        'https://apidocs.bitrix24.com/api-reference/sale/cashbox/sale-cashbox-update.html',
        'Batch update cash registers'
    )]
    public function update(array $cashboxes): Generator
    {
        $items = [];
        foreach ($cashboxes as $id => $cashbox) {
            $items[$id] = ['FIELDS' => $cashbox];
        }

        foreach ($this->batch->updateEntityItems('sale.cashbox.update', $items) as $key => $item) {
            yield $key => new UpdatedItemBatchResult($item);
        }
    }

    /**
     * Batch delete cash registers
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/cashbox/sale-cashbox-delete.html
     *
     * @param int[] $cashboxId
     *
     * @return Generator<int, DeletedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.cashbox.delete',
        'https://apidocs.bitrix24.com/api-reference/sale/cashbox/sale-cashbox-delete.html',
        'Batch delete cash registers'
    )]
    public function delete(array $cashboxId): Generator
    {
        foreach ($this->batch->deleteEntityItems('sale.cashbox.delete', $cashboxId) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }
}
