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

namespace Bitrix24\SDK\Services\Sale\PersonType\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Core\Result\AddedItemResult;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Services\Sale\PersonType\Result\CommentitemsResult;
use Bitrix24\SDK\Services\Sale\PersonType\Result\CommentitemResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['sale']))]
class PersonType extends AbstractService
{
    /**
     * Adds a comment to a task
     *
     * @link https://apidocs.bitrix24.com/api-reference/tasks/comment-item/task-comment-item-add.html
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
        'task.commentitem.add',
        'https://apidocs.bitrix24.com/api-reference/tasks/comment-item/task-comment-item-add.html',
        'Adds a comment to a task'
    )]
    public function add(int $taskId, array $fields): AddedItemResult
    {
        return new AddedItemResult(
            $this->core->call(
                'task.commentitem.add',
                [
                    'TASKID' => $taskId,
                    'FIELDS' => $fields
                ]
            )
        );
    }

    /**
     * Deletes a comment item.
     *
     * @link https://apidocs.bitrix24.com/api-reference/tasks/comment-item/task-comment-item-delete.html
     *
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'task.commentitem.delete',
        'https://apidocs.bitrix24.com/api-reference/tasks/comment-item/task-comment-item-delete.html',
        'Deletes a comment item.'
    )]
    public function delete(int $taskId, int $itemId): DeletedItemResult
    {
        return new DeletedItemResult(
            $this->core->call(
                'task.commentitem.delete',
                [
                    'TASKID' => $taskId,
                    'ITEMID' => $itemId,
                ]
            )
        );
    }

    /**
     * Retrieves a comment item by its id.
     *
     * @link https://apidocs.bitrix24.com/api-reference/tasks/comment-item/task-comment-item-get.html
     *
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'task.commentitem.get',
        'https://apidocs.bitrix24.com/api-reference/tasks/comment-item/task-comment-item-get.html',
        'Retrieves a comment item by its id'
    )]
    public function get(int $taskId, int $itemId): CommentitemResult
    {
        return new CommentitemResult(
            $this->core->call(
                'task.commentitem.get',
                [
                    'TASKID' => $taskId,
                    'ITEMID' => $itemId,
                ]
            )
        );
    }

    /**
     * Retrieves a list of comment items in the task.
     *
     * @link https://apidocs.bitrix24.com/api-reference/tasks/comment-item/task-comment-item-get-list.html
     *
     * $param ?array {
     *  ID,
     *  AUTHOR_ID,
     *  AUTHOR_NAME,
     *  AUTHOR_EMAIL,
     *  POST_DATE,
     *  } $order
     * $param ?array {
     *  ID,
     *  AUTHOR_ID,
     *  AUTHOR_NAME,
     *  POST_DATE,
     *  } $filter
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'task.commentitem.getlist',
        'https://apidocs.bitrix24.com/api-reference/tasks/comment-item/task-comment-item-get-list.html',
        'Retrieves a list of comment items in the task.'
    )]
    public function getList(int $taskId, ?array $order = [], ?array $filter = []): CommentitemsResult
    {
        return new CommentitemsResult(
            $this->core->call(
                'task.commentitem.getlist',
                [
                    'TASKID' => $taskId,
                    'ORDER' => $order,
                    'FILTER' => $filter,
                ]
            )
        );
    }

    /**
     * Updates the data of a comment item.
     *
     * @link https://apidocs.bitrix24.com/api-reference/tasks/comment-item/task-comment-item-update.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'task.commentitem.update',
        'https://apidocs.bitrix24.com/api-reference/tasks/comment-item/task-comment-item-update.html',
        'Updates the data of a comment item.'
    )]
    public function update(int $taskId, int $itemId, array $fields): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call(
                'task.commentitem.update',
                [
                    'TASKID' => $taskId,
                    'ITEMID' => $itemId,
                    'FIELDS' => $fields,
                ]
            )
        );
    }

}
