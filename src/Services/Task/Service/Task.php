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
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Contracts\SelectBuilderInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Task\Result\DeletedTaskResult;
use Bitrix24\SDK\Services\Task\Result\TaskResult;
use Bitrix24\SDK\Services\Task\Result\UpdatedTaskResult;
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
     * Returns a task by the task ID.
     *
     * @link https://apidocs.bitrix24.com/api-reference/rest-v3/tasks/tasks-task-get.html
     *
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'tasks.task.get',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/tasks/tasks-task-get.html',
        'Returns a task by the task ID',
        ApiVersion::v3
    )]
    /**
     * @param positive-int $id Task ID.
     * @param array<int,string>|TaskItemSelectBuilder $select
     */
    public function get(int $id, array|TaskItemSelectBuilder $select = []): TaskResult
    {
        if ($select instanceof SelectBuilderInterface) {
            $select = $select->buildSelect();
        }

        return new TaskResult($this->core->call('tasks.task.get', ['id' => $id, 'select' => $select], ApiVersion::v3));
    }

    /**
     * Add new task
     *
     * @link https://apidocs.bitrix24.com/api-reference/rest-v3/tasks/tasks-task-add.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'tasks.task.add',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/tasks/tasks-task-add.html',
        'Method adds new task',
        ApiVersion::v3
    )]
    public function add(array|TaskItemBuilder $fields): TaskResult
    {
        if ($fields instanceof TaskItemBuilder) {
            $fields = $fields->build();
        }

        return new TaskResult(
            $this->core->call(
                'tasks.task.add',
                [
                    'fields' => $fields
                ],
                ApiVersion::v3
            )
        );
    }

    /**
     * Deletes a task.
     *
     * @link https://apidocs.bitrix24.com/api-reference/rest-v3/tasks/tasks-task-delete.html
     *
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'tasks.task.delete',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/tasks/tasks-task-delete.html',
        'Deletes a task.',
        ApiVersion::v3
    )]
    public function delete(int $id): DeletedTaskResult
    {
        return new DeletedTaskResult(
            $this->core->call(
                'tasks.task.delete',
                [
                    'id' => $id,
                ],
                ApiVersion::v3
            )
        );
    }

    /**
     * Update task
     *
     * @link https://apidocs.bitrix24.com/api-reference/rest-v3/tasks/tasks-task-update.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'tasks.task.add',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/tasks/tasks-task-update.html',
        'Method update task',
        ApiVersion::v3
    )]
    public function update(int $id, array|TaskItemBuilder $fields): UpdatedTaskResult
    {
        if ($fields instanceof TaskItemBuilder) {
            $fields = $fields->build();
            unset($fields['creatorId']);
        }

        return new UpdatedTaskResult(
            $this->core->call(
                'tasks.task.update',
                [
                    'id' => $id,
                    'fields' => $fields
                ],
                ApiVersion::v3
            )
        );
    }

}
