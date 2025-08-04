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

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\AddedItemResult;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Core\Result\FieldsResult;
use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Task\Result\TasksResult;
use Bitrix24\SDK\Services\Task\Result\TaskResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['task']))]
class Task extends AbstractService
{
    /**
     * Task constructor.
     */
    public function __construct(public Batch $batch, CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Add new task
     *
     * @link https://apidocs.bitrix24.com/api-reference/tasks/tasks-task-add.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'tasks.task.add',
        'https://apidocs.bitrix24.com/api-reference/tasks/tasks-task-add.html',
        'Method adds new task'
    )]
    public function add(array $fields): AddedItemResult
    {
        return new AddedItemResult(
            $this->core->call(
                'tasks.task.add',
                [
                    'fields' => $fields
                ]
            )
        );
    }

    /**
     * Deletes a task.
     *
     * @link https://apidocs.bitrix24.com/api-reference/tasks/tasks-task-delete.html
     *
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'tasks.task.delete',
        'https://apidocs.bitrix24.com/api-reference/tasks/tasks-task-delete.html',
        'Deletes a task.'
    )]
    public function delete(int $id): DeletedItemResult
    {
        return new DeletedItemResult(
            $this->core->call(
                'tasks.task.delete',
                [
                    'taskId' => $id,
                ]
            )
        );
    }

    /**
     * Get the task fields reference.
     *
     * @link https://apidocs.bitrix24.com/api-reference/tasks/tasks-task-get-fields.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'tasks.task.getFields',
        'https://apidocs.bitrix24.com/api-reference/tasks/tasks-task-get-fields.html',
        'Get the task fields reference.'
    )]
    public function fields(): FieldsResult
    {
        return new FieldsResult($this->core->call('tasks.task.getFields'));
    }
    
    /**
     * Returns a task by the task ID.
     *
     * @link https://apidocs.bitrix24.com/api-reference/tasks/tasks-task-get.html
     *
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'tasks.task.get',
        'https://apidocs.bitrix24.com/api-reference/tasks/tasks-task-get.html',
        'Returns a task by the task ID'
    )]
    public function get(int $id, array $select = ['*']): TaskResult
    {
        return new TaskResult($this->core->call('tasks.task.get', ['taskId' => $id, 'select' => $select]));
    }

    /**
     * Retrieve a list of tasks.
     *
     * @link https://apidocs.bitrix24.com/api-reference/tasks/tasks-task-list.html
     * 
     * $param array {
     *  ID,
     *  TITLE,
     *  TIME_SPENT_IN_LOGS,
     *  DATE_START,
     *  CREATED_DATE,
     *  CHANGED_DATE,
     *  CLOSED_DATE,
     *  START_DATE_PLAN,
     *  END_DATE_PLAN,
     *  DEADLINE,
     *  REAL_STATUS,
     *  STATUS_COMPLETE,
     *  PRIORITY,
     *  MARK,
     *  CREATED_BY_LAST_NAME,
     *  RESPONSIBLE_LAST_NAME,
     *  GROUP_ID,
     *  TIME_ESTIMATE,
     *  ALLOW_CHANGE_DEADLINE,
     *  ALLOW_TIME_TRACKING,
     *  MATCH_WORK_TIME,
     *  FAVORITE,
     *  SORTING,
     *  MESSAGE_ID, 
     *  } $order
     * @param array{
     *   ID,
     *   PARENT_ID,
     *   GROUP_ID,
     *   CREATED_BY,
     *   STATUS_CHANGED_BY,
     *   PRIORITY,
     *   FORUM_TOPIC_ID,
     *   RESPONSIBLE_ID,
     *   TITLE,
     *   TAG,
     *   REAL_STATUS,
     *   STATUS,
     *   MARK,
     *   SITE_ID,
     *   ADD_IN_REPORT,
     *   DATE_START,
     *   DEADLINE,
     *   CREATED_DATE,
     *   CLOSED_DATE,
     *   CHANGED_DATE,
     *   ACCOMPLICE,
     *   AUDITOR,
     *   DEPENDS_ON,
     *   ONLY_ROOT_TASKS,
     *   STAGE_ID,
     *   UF_CRM_TASK ,
     *   } $filter
     * @param array $select = ['ID','PARENT_ID','TITLE','DESCRIPTION','MARK','PRIORITY','STATUS','MULTITASK','NOT_VIEWED','REPLICATE','GROUP_ID','STAGE_ID','CREATED_BY','CREATED_DATE','RESPONSIBLE_ID','ACCOMPLICES','AUDITORS','CHANGED_BY','CHANGED_DATE','STATUS_CHANGED_BY','STATUS_CHANGED_DATE','CLOSED_BY','CLOSED_DATE','DATE_START','DEADLINE','START_DATE_PLAN','END_DATE_PLAN','GUID','XML_ID','COMMENTS_COUNT','NEW_COMMENTS_COUNT','TASK_CONTROL','ADD_IN_REPORT','FORKED_BY_TEMPLATE_ID','TIME_ESTIMATE','TIME_SPENT_IN_LOGS','MATCH_WORK_TIME','FORUM_TOPIC_ID','FORUM_ID','SITE_ID','SUBORDINATE','FAVORITE','VIEWED_DATE','SORTING','DURATION_PLAN','DURATION_FACT','DURATION_TYPE']
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'tasks.task.list',
        'https://apidocs.bitrix24.com/api-reference/tasks/tasks-task-list.html',
        'Retrieve a list of tasks.'
    )]
    public function list(array $order = [], array $filter = [], array $select = [], $start = 0, int $limit = 50): TasksResult
    {
        $params = $filter;
        $params['order'] = $order;
        $params['filter'] = $filter;
        $params['select'] = $select;
        $params['limit'] = $limit;
        $params['start'] = $start;
        
        return new TasksResult($this->core->call('tasks.task.list', $params));
    }

    /**
     * Updates the specified (existing) task.
     *
     * @link https://apidocs.bitrix24.com/api-reference/tasks/tasks-task-update.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'tasks.task.update',
        'https://apidocs.bitrix24.com/api-reference/tasks/tasks-task-update.html',
        'Updates the specified (existing) task.'
    )]
    public function update(int $id, array $fields): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call(
                'tasks.task.update',
                [
                    'taskId' => $id,
                    'fields' => $fields
                ]
            )
        );
    }

    /**
     * Count tasks by filter
     *
     * @param array{
     *   ID,
     *   PARENT_ID,
     *   GROUP_ID,
     *   CREATED_BY,
     *   STATUS_CHANGED_BY,
     *   PRIORITY,
     *   FORUM_TOPIC_ID,
     *   RESPONSIBLE_ID,
     *   TITLE,
     *   TAG,
     *   REAL_STATUS,
     *   STATUS,
     *   MARK,
     *   SITE_ID,
     *   ADD_IN_REPORT,
     *   DATE_START,
     *   DEADLINE,
     *   CREATED_DATE,
     *   CLOSED_DATE,
     *   CHANGED_DATE,
     *   ACCOMPLICE,
     *   AUDITOR,
     *   DEPENDS_ON,
     *   ONLY_ROOT_TASKS,
     *   STAGE_ID,
     *   UF_CRM_TASK ,
     *   } $filter
     *
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     * @throws \Bitrix24\SDK\Core\Exceptions\TransportException
     */
    public function countByFilter(array $filter = []): int
    {
        return $this->list([], $filter, ['ID'], 1)->getCoreResponse()->getResponseData()->getPagination()->getTotal();
    }
    
    /**
     * Delegates the specified (existing) task.
     *
     * @link https://apidocs.bitrix24.com/api-reference/tasks/tasks-task-delegate.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'tasks.task.update',
        'https://apidocs.bitrix24.com/api-reference/tasks/tasks-task-delegate.html',
        'Delegates the specified (existing) task.'
    )]
    public function delegate(int $id, int $userId): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call(
                'tasks.task.delegate',
                [
                    'taskId' => $id,
                    'userId' => $userId
                ]
            )
        );
    }
    
    /**
     * Starts the specified (existing) task.
     *
     * @link https://apidocs.bitrix24.com/api-reference/tasks/tasks-task-start.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'tasks.task.start',
        'https://apidocs.bitrix24.com/api-reference/tasks/tasks-task-start.html',
        'Starts the specified (existing) task.'
    )]
    public function start(int $id): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call(
                'tasks.task.start',
                [
                    'taskId' => $id,
                ]
            )
        );
    }
    
    /**
     * Pauses the specified (existing) task.
     *
     * @link https://apidocs.bitrix24.com/api-reference/tasks/tasks-task-pause.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'tasks.task.pause',
        'https://apidocs.bitrix24.com/api-reference/tasks/tasks-task-pause.html',
        'Pauses the specified (existing) task.'
    )]
    public function pause(int $id): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call(
                'tasks.task.pause',
                [
                    'taskId' => $id,
                ]
            )
        );
    }
    
    /**
     * Changes the task status to "deferred".
     *
     * @link https://apidocs.bitrix24.com/api-reference/tasks/tasks-task-defer.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'tasks.task.defer',
        'https://apidocs.bitrix24.com/api-reference/tasks/tasks-task-defer.html',
        'Changes the task status to "deferred".'
    )]
    public function defer(int $id): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call(
                'tasks.task.defer',
                [
                    'taskId' => $id,
                ]
            )
        );
    }
    
    /**
     * Changes the task status to "completed".
     *
     * @link https://apidocs.bitrix24.com/api-reference/tasks/tasks-task-complete.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'tasks.task.complete',
        'https://apidocs.bitrix24.com/api-reference/tasks/tasks-task-complete.html',
        'Changes the task status to "completed".'
    )]
    public function complete(int $id): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call(
                'tasks.task.complete',
                [
                    'taskId' => $id,
                ]
            )
        );
    }
    
    /**
     * Renews a task after it has been completed.
     *
     * @link https://apidocs.bitrix24.com/api-reference/tasks/tasks-task-renew.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'tasks.task.renew',
        'https://apidocs.bitrix24.com/api-reference/tasks/tasks-task-renew.html',
        'Renews a task after it has been completed.'
    )]
    public function renew(int $id): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call(
                'tasks.task.renew',
                [
                    'taskId' => $id,
                ]
            )
        );
    }
    
    /**
     * Approves a task.
     *
     * @link https://apidocs.bitrix24.com/api-reference/tasks/tasks-task-approve.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'tasks.task.approve',
        'https://apidocs.bitrix24.com/api-reference/tasks/tasks-task-approve.html',
        'Approves a task.'
    )]
    public function approve(int $id): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call(
                'tasks.task.approve',
                [
                    'taskId' => $id,
                ]
            )
        );
    }
    
    /**
     * Rejects a task.
     *
     * @link https://apidocs.bitrix24.com/api-reference/tasks/tasks-task-disapprove.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'tasks.task.disapprove',
        'https://apidocs.bitrix24.com/api-reference/tasks/tasks-task-disapprove.html',
        'Rejects a task.'
    )]
    public function disapprove(int $id): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call(
                'tasks.task.disapprove',
                [
                    'taskId' => $id,
                ]
            )
        );
    }
    
    /**
     * Allows watching a task.
     *
     * @link https://apidocs.bitrix24.com/api-reference/tasks/tasks-task-start-watch.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'tasks.task.startwatch',
        'https://apidocs.bitrix24.com/api-reference/tasks/tasks-task-start-watch.html',
        'Allows watching a task.'
    )]
    public function startwatch(int $id): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call(
                'tasks.task.startwatch',
                [
                    'taskId' => $id,
                ]
            )
        );
    }
    
    /**
     * Stops watching a task.
     *
     * @link https://apidocs.bitrix24.com/api-reference/tasks/tasks-task-stop-watch.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'tasks.task.stopwatch',
        'https://apidocs.bitrix24.com/api-reference/tasks/tasks-task-stop-watch.html',
        'Stops watching a task.'
    )]
    public function stopwatch(int $id): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call(
                'tasks.task.stopwatch',
                [
                    'taskId' => $id,
                ]
            )
        );
    }
    
    /**
     * Enables "Silent" mode.
     *
     * @link https://apidocs.bitrix24.com/api-reference/tasks/tasks-task-mute.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'tasks.task.mute',
        'https://apidocs.bitrix24.com/api-reference/tasks/tasks-task-mute.html',
        'Enables "Silent" mode.'
    )]
    public function mute(int $id): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call(
                'tasks.task.mute',
                [
                    'id' => $id,
                ]
            )
        );
    }
    
    /**
     * Disables "Silent" mode.
     *
     * @link https://apidocs.bitrix24.com/api-reference/tasks/tasks-task-unmute.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'tasks.task.unmute',
        'https://apidocs.bitrix24.com/api-reference/tasks/tasks-task-unmute.html',
        'Disables "Silent" mode.'
    )]
    public function unmute(int $id): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call(
                'tasks.task.unmute',
                [
                    'id' => $id,
                ]
            )
        );
    }
    
    /**
     * Adds tasks to favorites.
     *
     * @link https://apidocs.bitrix24.com/api-reference/tasks/tasks-task-favorite-add.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'tasks.task.favorite.add',
        'https://apidocs.bitrix24.com/api-reference/tasks/tasks-task-favorite-add.html',
        'Adds tasks to favorites.'
    )]
    public function addFavorite(int $id): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call(
                'tasks.task.favorite.add',
                [
                    'taskId' => $id,
                ]
            )
        );
    }
    
    /**
     * Removes tasks from favorites.
     *
     * @link https://apidocs.bitrix24.com/api-reference/tasks/tasks-task-favorite-remove.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'tasks.task.favorite.remove',
        'https://apidocs.bitrix24.com/api-reference/tasks/tasks-task-favorite-remove.html',
        'Removes tasks from favorites.'
    )]
    public function removeFavorite(int $id): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call(
                'tasks.task.favorite.remove',
                [
                    'taskId' => $id,
                ]
            )
        );
    }
    
    /**
     * Retrieves user counters.
     *
     * @link https://apidocs.bitrix24.com/api-reference/tasks/tasks-task-counters-get.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'tasks.task.counters.get',
        'https://apidocs.bitrix24.com/api-reference/tasks/tasks-task-counters-get.html',
        'Retrieves user counters.'
    )]
    public function getCounters(int $userId, int $groupId = 0, string $type = 'view_all'): CountersResult
    {
        return new CountersResult(
            $this->core->call(
                'tasks.task.counters.get',
                [
                    'userId' => $userId,
                    'groupId' => $groupId,
                    'type' => $type,
                ]
            )
        );
    }
    
    /**
     * Checks access to a task.
     *
     * @link https://apidocs.bitrix24.com/api-reference/tasks/tasks-task-get-access.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'tasks.task.getaccess',
        'https://apidocs.bitrix24.com/api-reference/tasks/tasks-task-get-access.html',
        'Checks access to a task.'
    )]
    public function getAccess(int $taskId, array $userIds = []): AccessesResult
    {
        return new AccessesResult(
            $this->core->call(
                'tasks.task.getaccess',
                [
                    'taskId' => $taskId,
                    'users' => $userIds,
                ]
            )
        );
    }
    
    /**
     * Creates a dependency of one task on another.
     *
     * @link https://apidocs.bitrix24.com/api-reference/tasks/task-dependence-add.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'task.dependence.add',
        'https://apidocs.bitrix24.com/api-reference/tasks/task-dependence-add.html',
        'Creates a dependency of one task on another.'
    )]
    public function addDependence(int $taskIdFrom, int $taskIdTo, int $linkType): DependenceResult
    {
        return new DependenceResult(
            $this->core->call(
                'task.dependence.add',
                [
                    'taskIdFrom' => $taskIdFrom,
                    'taskIdTo' => $taskIdTo,
                    'linkType' => $linkType,
                ]
            )
        );
    }
    
    /**
     * Deletes a dependency of one task from another.
     *
     * @link https://apidocs.bitrix24.com/api-reference/tasks/task-dependence-delete.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'task.dependence.delete',
        'https://apidocs.bitrix24.com/api-reference/tasks/task-dependence-delete.html',
        'Deletes a dependency of one task from another.'
    )]
    public function deleteDependence(int $taskIdFrom, int $taskIdTo): DependenceResult
    {
        return new DependenceResult(
            $this->core->call(
                'task.dependence.delete',
                [
                    'taskIdFrom' => $taskIdFrom,
                    'taskIdTo' => $taskIdTo,
                ]
            )
        );
    }
    
    /**
     * Retrieves task history.
     *
     * @link https://apidocs.bitrix24.com/api-reference/tasks/tasks-task-history-list.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'tasks.task.history.list',
        'https://apidocs.bitrix24.com/api-reference/tasks/tasks-task-history-list.html',
        'Retrieves task history.'
    )]
    public function historyList(int $id, int $start = 0): HistoriesResult
    {
        return new HistoriesResult(
            $this->core->call(
                'tasks.task.history.list',
                [
                    'taskId' => $id,
                    'start' => $start,
                ]
            )
        );
    }
}
