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

namespace Bitrix24\SDK\Services\Task\TaskResult\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Task\TaskResult\Result\AddedResultResult;
use Bitrix24\SDK\Services\Task\TaskResult\Result\DeletedResultResult;
use Bitrix24\SDK\Services\Task\TaskResult\Result\ResultsResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['task']))]
class Result extends AbstractService
{
    /**
     * Adds a comment to a task result
     *
     * @link https://apidocs.bitrix24.com/api-reference/tasks/result/tasks-task-result-add-from-comment.html
     *
     * $param array {
     *  POST_MESSAGE,
     *  AUTHOR_ID,
     *  POST_DATE,
     *  UF_FORUM_MESSAGE_DOC,
     *  } $fields
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'tasks.task.result.addFromComment',
        'https://apidocs.bitrix24.com/api-reference/tasks/result/tasks-task-result-add-from-comment.html',
        'Adds a comment to a task result'
    )]
    public function addFromComment(int $commentId): AddedResultResult
    {
        return new AddedResultResult(
            $this->core->call(
                'tasks.task.result.addFromComment',
                [
                    'commentId' => $commentId,
                ]
            )
        );
    }

    /**
     * Deletes a task result.
     *
     * @link https://apidocs.bitrix24.com/api-reference/tasks/result/tasks-task-result-delete-from-comment.html
     *
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'tasks.task.result.deleteFromComment',
        'https://apidocs.bitrix24.com/api-reference/tasks/result/tasks-task-result-delete-from-comment.html',
        'Deletes a task result.'
    )]
    public function deleteFromComment(int $commentId): DeletedResultResult
    {
        return new DeletedResultResult(
            $this->core->call(
                'tasks.task.result.deleteFromComment',
                [
                    'commentId' => $commentId,
                ]
            )
        );
    }

    /**
     * Retrieves a list of results in the task.
     *
     * @link https://apidocs.bitrix24.com/api-reference/tasks/result/tasks-task-result-list.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'tasks.task.result.list',
        'https://apidocs.bitrix24.com/api-reference/tasks/result/tasks-task-result-list.html',
        'Retrieves a list of results in the task.'
    )]
    public function list(int $taskId): ResultsResult
    {
        return new ResultsResult(
            $this->core->call(
                'tasks.task.result.list',
                [
                    'taskId' => $taskId,
                ]
            )
        );
    }
    
}
