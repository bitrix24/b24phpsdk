<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Vadim Soluyanov <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\CRM\Status\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AddedItemBatchResult;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Core\Result\UpdatedItemBatchResult;
use Bitrix24\SDK\Services\CRM\Status\Result\StatusItemResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['crm']))]
class Batch
{
    /**
     * Batch constructor.
     */
    public function __construct(protected BatchOperationsInterface $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch list method for statuses
     *
     * @param array{
     *   ID?: int,
     *   ENTITY_ID?: string,
     *   STATUS_ID?: string,
     *   SORT?: int,
     *   NAME?: string,
     *   NAME_INIT?: string,
     *   SYSTEM?: bool,
     *   CATEGORY_ID?: int,
     *   COLOR?: string,
     *   SEMANTICS?: string,
     *   EXTRA?: array,
     *   } $order
     *
     * @param array{
     *   ID?: int,
     *   ENTITY_ID?: string,
     *   STATUS_ID?: string,
     *   SORT?: int,
     *   NAME?: string,
     *   NAME_INIT?: string,
     *   SYSTEM?: bool,
     *   CATEGORY_ID?: int,
     *   COLOR?: string,
     *   SEMANTICS?: string,
     *   EXTRA?: array,
     *   } $filter
     * @param array    $select = ['ID','ENTITY_ID','STATUS_ID','SORT','NAME','NAME_INIT','SYSTEM','CATEGORY_ID','COLOR','SEMANTICS','EXTRA']
     *
     * @return Generator<int, StatusItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'crm.status.list',
        'https://apidocs.bitrix24.com/api-reference/crm/status/crm-status-list.html',
        'Batch list method for statuses'
    )]
    public function list(array $order, array $filter, array $select, ?int $limit = null): Generator
    {
        $this->log->debug(
            'batchList',
            [
                'order'  => $order,
                'filter' => $filter,
                'select' => $select,
                'limit'  => $limit,
            ]
        );
        foreach ($this->batch->getTraversableList('crm.status.list', $order, $filter, $select, $limit) as $key => $value) {
            yield $key => new StatusItemResult($value);
        }
    }

    /**
     * Batch adding statuses
     *
     * @param array <int, array{
     *   ID?: int,
     *   ENTITY_ID?: string,
     *   STATUS_ID?: string,
     *   SORT?: int,
     *   NAME?: string,
     *   CATEGORY_ID?: int,
     *   COLOR?: string,
     *   SEMANTICS?: string,
     *   EXTRA?: array,
     *   }> $statuses
     *
     * @return Generator<int, AddedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'crm.status.add',
        'https://apidocs.bitrix24.com/api-reference/crm/status/crm-status-add.html',
        'Batch adding statuses'
    )]
    public function add(array $statuses): Generator
    {
        $items = [];
        foreach ($statuses as $status) {
            $items[] = [
                'fields' => $status,
            ];
        }

        foreach ($this->batch->addEntityItems('crm.status.add', $items) as $key => $item) {
            yield $key => new AddedItemBatchResult($item);
        }
    }

    /**
     * Batch update statuses
     *
     * Update elements in array with structure
     * element_id => [  // status id
     *  'fields' => [] // status fields to update
     * ]
     *
     * @param array<int, array> $entityItems
     * @return Generator<int, UpdatedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'crm.status.update',
        'https://apidocs.bitrix24.com/api-reference/crm/status/crm-status-update.html',
        'Update in batch mode a list of statuses'
    )]
    public function update(array $entityItems): Generator
    {
        foreach ($this->batch->updateEntityItems('crm.status.update', $entityItems) as $key => $item) {
            yield $key => new UpdatedItemBatchResult($item);
        }
    }

    /**
     * Batch delete statuses
     *
     * @param int[] $statusId
     *
     * @return Generator<int, DeletedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'crm.status.delete',
        'https://apidocs.bitrix24.com/api-reference/crm/status/crm-status-delete.html',
        'Batch delete statuses'
    )]
    public function delete(array $statusId): Generator
    {
        foreach ($this->batch->deleteEntityItems('crm.status.delete', $statusId) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }
}
