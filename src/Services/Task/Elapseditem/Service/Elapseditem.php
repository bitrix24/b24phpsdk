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

namespace Bitrix24\SDK\Services\Task\Elapseditem\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Core\Result\AddedItemResult;
use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Services\Task\Elapseditem\Result\DeletedElapseditemResult;
use Bitrix24\SDK\Services\Task\Elapseditem\Result\ElapseditemsResult;
use Bitrix24\SDK\Services\Task\Elapseditem\Result\ElapseditemResult;
use Bitrix24\SDK\Services\Task\Elapseditem\Result\UpdatedElapseditemResult;
use Bitrix24\SDK\Services\Task\Elapseditem\Result\ManifestItemResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['task']))]
class Elapseditem extends AbstractService
{
    /**
     * Adds time spent to a task.
     *
     * @link https://apidocs.bitrix24.com/api-reference/tasks/elapsed-item/task-elapsed-item-add.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'task.elapseditem.add',
        'https://apidocs.bitrix24.com/api-reference/tasks/elapsed-item/task-elapsed-item-add.html',
        'Adds time spent to a task.'
    )]
    public function add(int $taskId, int $seconds, string $text, ?int $userId = null): AddedItemResult
    {
        $params = [
            'SECONDS' => $seconds,
            'COMMENT_TEXT' => $text,
        ];
        if ($userId !== null && $userId !== 0) {
            $params['USER_ID'] = $userId;
        }

        return new AddedItemResult(
            $this->core->call(
                'task.elapseditem.add',
                [
                    'TASKID' => $taskId,
                    'ARFIELDS' => $params
                ]
            )
        );
    }

    /**
     * Deletes a time spent entry.
     *
     * @link https://apidocs.bitrix24.com/api-reference/tasks/elapsed-item/task-elapsed-item-delete.html
     *
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'task.elapseditem.delete',
        'https://apidocs.bitrix24.com/api-reference/tasks/elapsed-item/task-elapsed-item-delete.html',
        'Deletes a time spent entry.'
    )]
    public function delete(int $taskId, int $itemId): DeletedElapseditemResult
    {
        return new DeletedElapseditemResult(
            $this->core->call(
                'task.elapseditem.delete',
                [
                    'TASKID' => $taskId,
                    'ITEMID' => $itemId,
                ]
            )
        );
    }

    /**
     * Returns a time spent entry by its identifier.
     *
     * @link https://apidocs.bitrix24.com/api-reference/tasks/elapsed-item/task-elapsed-item-get.html
     *
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'task.elapseditem.get',
        'https://apidocs.bitrix24.com/api-reference/tasks/elapsed-item/task-elapsed-item-get.html',
        'Returns a time spent entry by its identifier.'
    )]
    public function get(int $taskId, int $itemId): ElapseditemResult
    {
        return new ElapseditemResult(
            $this->core->call(
                'task.elapseditem.get',
                [
                    'TASKID' => $taskId,
                    'ITEMID' => $itemId,
                ]
            )
        );
    }

    /**
     * Returns a list of time spent entries for a task.
     *
     * @link https://apidocs.bitrix24.com/api-reference/tasks/elapsed-item/task-elapsed-item-get-list.html
     *
     * $param ?array {
     *  ID,
     *  CREATED_BY,
     *  TOGGLED_BY,
     *  TOGGLED_DATE,
     *  TITLE,
     *  SORT_INDEX,
     *  IS_COMPLETE,
     *  } $order
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'task.elapseditem.getlist',
        'https://apidocs.bitrix24.com/api-reference/tasks/elapsed-item/task-elapsed-item-get-list.html',
        'Returns a list of time spent entries for a task.'
    )]
    public function getList(int $taskId, ?array $order = [], ?array $filter = [], ?array $select = [], ?int $page = 1, ?int $pageSize = 50): ElapseditemsResult
    {
        return new ElapseditemsResult(
            $this->core->call(
                'task.elapseditem.getlist',
                [
                    'TASKID' => $taskId,
                    'ORDER' => $order,
                    'FILTER' => $filter,
                    'SELECT' => $select,
                    'PARAMS' => [
                        'NAV_PARAMS' => [
                            'nPageSize' => $pageSize,
                            'iNumPage' => $page,
                        ]
                    ]
                ]
            )
        );
    }

    /**
     * Modifies the parameters of a time spent entry.
     *
     * @link https://apidocs.bitrix24.com/api-reference/tasks/elapsed-item/task-elapsed-item-update.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'task.elapseditem.update',
        'https://apidocs.bitrix24.com/api-reference/tasks/elapsed-item/task-elapsed-item-update.html',
        'Modifies the parameters of a time spent entry.'
    )]
    public function update(int $taskId, int $itemId, array $fields): UpdatedElapseditemResult
    {
        return new UpdatedElapseditemResult(
            $this->core->call(
                'task.elapseditem.update',
                [
                    'TASKID' => $taskId,
                    'ITEMID' => $itemId,
                    'ARFIELDS' => $fields,
                ]
            )
        );
    }

    /**
     * Checks if the action is allowed.
     *
     * ActionId can be taken
     * 1 - add a new record (ACTION_ELAPSED_TIME_ADD)
     * 2 - modify a record (ACTION_ELAPSED_TIME_MODIFY)
     * 3 - delete a record (ACTION_ELAPSED_TIME_REMOVE)
     *
     * @link https://apidocs.bitrix24.com/api-reference/tasks/elapsed-item/task-elapsed-item-is-action-allowed.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'task.elapseditem.isactionallowed',
        'https://apidocs.bitrix24.com/api-reference/tasks/elapsed-item/task-elapsed-item-is-action-allowed.html',
        'Checks if the action is allowed.'
    )]
    public function isActionAllowed(int $taskId, int $itemId, int $actionId): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call(
                'task.elapseditem.isactionallowed',
                [
                    'TASKID' => $taskId,
                    'ITEMID' => $itemId,
                    'ACTIONID' => $actionId,
                ]
            )
        );
    }

    /**
     * Retrieves a list of methods and their descriptions.
     *
     * @link https://apidocs.bitrix24.com/api-reference/tasks/elapsed-item/task-elapsed-item-get-manifest.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'task.elapseditem.getmanifest',
        'https://apidocs.bitrix24.com/api-reference/tasks/elapsed-item/task-elapsed-item-get-manifest.html',
        'Retrieves a list of methods and their descriptions.'
    )]
    public function getManifest(): array
    {
        return $this->core->call('task.elapseditem.getmanifest', [])->getResponseData()->getResult();
    }

}
