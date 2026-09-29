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

namespace Bitrix24\SDK\Services\Sale\CashboxHandler\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AddedItemBatchResult;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Core\Result\UpdatedItemBatchResult;
use Bitrix24\SDK\Services\Sale\CashboxHandler\Result\CashboxHandlerItemResult;
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
     * Batch list method for cash register handlers
     *
     * The sale.cashbox.handler.list method does not support pagination, all items are returned by a single call.
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/cashbox/sale-cashbox-handler-list.html
     *
     * @param int|null $limit Maximum number of items to return
     *
     * @return Generator<int, CashboxHandlerItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.cashbox.handler.list',
        'https://apidocs.bitrix24.com/api-reference/sale/cashbox/sale-cashbox-handler-list.html',
        'Batch list method for cash register handlers'
    )]
    public function list(?int $limit = null): Generator
    {
        $this->log->debug(
            'batchList',
            [
                'limit' => $limit,
            ]
        );

        foreach ($this->batch->getTraversableList('sale.cashbox.handler.list', [], [], [], $limit) as $key => $value) {
            yield $key => new CashboxHandlerItemResult($value);
        }
    }

    /**
     * Batch adding cash register handlers
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/cashbox/sale-cashbox-handler-add.html
     *
     * @param array<int, array{
     *     CODE: string,
     *     NAME: string,
     *     SETTINGS: array,
     *     SORT?: int,
     *     SUPPORTS_FFD105?: string
     *   }> $handlers
     *
     * @return Generator<int, AddedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.cashbox.handler.add',
        'https://apidocs.bitrix24.com/api-reference/sale/cashbox/sale-cashbox-handler-add.html',
        'Batch adding cash register handlers'
    )]
    public function add(array $handlers): Generator
    {
        foreach ($this->batch->addEntityItems('sale.cashbox.handler.add', $handlers) as $key => $item) {
            yield $key => new AddedItemBatchResult($item);
        }
    }

    /**
     * Batch update cash register handlers
     *
     * Update elements in array with structure
     * element_id => [ // cash register handler id
     *  // cash register handler fields to update
     * ]
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/cashbox/sale-cashbox-handler-update.html
     *
     * @param array<int, array> $handlers keyed by cash register handler id
     *
     * @return Generator<int, UpdatedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.cashbox.handler.update',
        'https://apidocs.bitrix24.com/api-reference/sale/cashbox/sale-cashbox-handler-update.html',
        'Batch update cash register handlers'
    )]
    public function update(array $handlers): Generator
    {
        $items = [];
        foreach ($handlers as $id => $handler) {
            $items[$id] = ['FIELDS' => $handler];
        }

        foreach ($this->batch->updateEntityItems('sale.cashbox.handler.update', $items) as $key => $item) {
            yield $key => new UpdatedItemBatchResult($item);
        }
    }

    /**
     * Batch delete cash register handlers
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/cashbox/sale-cashbox-handler-delete.html
     *
     * @param int[] $handlerId
     *
     * @return Generator<int, DeletedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'sale.cashbox.handler.delete',
        'https://apidocs.bitrix24.com/api-reference/sale/cashbox/sale-cashbox-handler-delete.html',
        'Batch delete cash register handlers'
    )]
    public function delete(array $handlerId): Generator
    {
        foreach ($this->batch->deleteEntityItems('sale.cashbox.handler.delete', $handlerId) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }
}
