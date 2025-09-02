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

namespace Bitrix24\SDK\Services\Task\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
//use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Services\Task;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Services\Task\Result\TaskItemResult;
use Bitrix24\SDK\Services\Task\Result\AddedTaskBatchResult;
use Bitrix24\SDK\Services\Task\Result\UpdatedTaskBatchResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['task']))]
class Batch
{
    /**
     * Batch constructor.
     */
    public function __construct(protected Task\Batch $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch list method for tasks
     *
     * @param array{
     *   ID?: int,
     *   PARENT_ID?: int,
     *   GROUP_ID?: int,
     *   CREATED_BY?: int,
     *   STATUS_CHANGED_BY?: int,
     *   PRIORITY?: int,
     *   FORUM_TOPIC_ID?: int,
     *   RESPONSIBLE_ID?: int,
     *   TITLE?: string,
     *   TAG?: string,
     *   REAL_STATUS?: int,
     *   STATUS?: int,
     *   MARK?: int,
     *   SITE_ID?: string,
     *   ADD_IN_REPORT?: string,
     *   DATE_START?: string,
     *   DEADLINE?: string,
     *   CREATED_DATE?: string,
     *   CLOSED_DATE?: string,
     *   CHANGED_DATE?: string,
     *   ACCOMPLICE?: int,
     *   AUDITOR?: int,
     *   DEPENDS_ON?: int,
     *   ONLY_ROOT_TASKS?: string,
     *   STAGE_ID?: string,
     *   UF_CRM_TASK?: array,
     *   } $filter
     *
     * @return Generator<int, TaskItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'tasks.task.list',
        'https://apidocs.bitrix24.com/api-reference/tasks/tasks-task-list.html',
        'Batch list method for tasks'
    )]
    public function list(array $order = [], array $filter = [], array $select = [], ?int $limit = null): Generator
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
        foreach ($this->batch->getTraversableList('tasks.task.list', $order, $filter, $select, $limit) as $key => $value) {
            yield $key => new TaskItemResult($value);
        }
    }

    /**
     * Batch adding tasks
     *
     * @param array <int, array> $tasks
     *
     * @return Generator<int, AddedTaskBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'tasks.task.add',
        'https://apidocs.bitrix24.com/api-reference/tasks/tasks-task-add.html',
        'Batch adding tasks'
    )]
    public function add(array $tasks): Generator
    {
        $items = [];
        foreach ($tasks as $key => $task) {
            $items[$key] = [
                'fields' => $task
            ];
        }

        foreach ($this->batch->addEntityItems('tasks.task.add', $items) as $key => $item) {
            yield $key => new AddedTaskBatchResult($item);
        }
    }

    /**
     * Batch update tasks
     *
     * Update elements in array with structure
     * element_id => [  // task id
     *  'fields' => [] // task fields to update
     * ]
     *
     * @param array<int, array> $taskItems
     * @return Generator<int, UpdatedTaskBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'tasks.task.update',
        'https://apidocs.bitrix24.com/api-reference/tasks/tasks-task-update.html',
        'Update in batch mode a list of tasks'
    )]
    public function update(array $taskItems): Generator
    {
        foreach ($this->batch->updateEntityItems('tasks.task.update', $taskItems) as $key => $item) {
            yield $key => new UpdatedTaskBatchResult($item);
        }
    }

    /**
     * Batch delete tasks
     *
     * @param int[] $taskIds
     *
     * @return Generator<int, DeletedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'tasks.task.delete',
        'https://apidocs.bitrix24.com/api-reference/tasks/tasks-task-delete.html',
        'Batch delete tasks'
    )]
    public function delete(array $taskIds): Generator
    {
        foreach ($this->batch->deleteEntityItems('tasks.task.delete', $taskIds) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }
}
