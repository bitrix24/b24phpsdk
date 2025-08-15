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

namespace Bitrix24\SDK\Services\Task\Stage\Service;

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
use Bitrix24\SDK\Services\Task\Stage\Result\StagesResult;
use Bitrix24\SDK\Services\Task\Stage\Result\StageResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['task']))]
class Stage extends AbstractService
{
    /**
     * Adds Kanban or "My Planner" stages
     *
     * @link https://apidocs.bitrix24.com/api-reference/tasks/stages/task-stages-add.html
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
        'task.stages.add',
        'https://apidocs.bitrix24.com/api-reference/tasks/stages/task-stages-add.html',
        'Adds Kanban or "My Planner" stages'
    )]
    public function add(array $fields, bool $isAdmin = false): AddedItemResult
    {
        return new AddedItemResult(
            $this->core->call(
                'task.stages.add',
                [
                    'fields' => $fields,
                    'isAdmin' => $isAdmin,
                ]
            )
        );
    }

    /**
     * Updates Kanban or "My Planner" stages.
     *
     * @link https://apidocs.bitrix24.com/api-reference/tasks/stages/task-stages-update.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'task.stages.update',
        'https://apidocs.bitrix24.com/api-reference/tasks/stages/task-stages-update.html',
        'Updates Kanban or "My Planner" stages.'
    )]
    public function update(int $id, array $fields, bool $isAdmin = false): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call(
                'task.stages.update',
                [
                    'id' => $id,
                    'fields' => $fields,
                    'isAdmin' => $isAdmin,
                ]
            )
        );
    }

    /**
     * Deletes Kanban or "My Planner" stages.
     *
     * @link https://apidocs.bitrix24.com/api-reference/tasks/stages/task-stages-delete.html
     *
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'task.stages.delete',
        'https://apidocs.bitrix24.com/api-reference/tasks/stages/task-stages-delete.html',
        'Deletes Kanban or "My Planner" stages.'
    )]
    public function delete(int $id, bool $isAdmin = false): DeletedItemResult
    {
        return new DeletedItemResult(
            $this->core->call(
                'task.stages.delete',
                [
                    'id' => $id,
                    'isAdmin' => $isAdmin,
                ]
            )
        );
    }

    /**
     * Retrieves Kanban or "My Planner" stages.
     *
     * @link https://apidocs.bitrix24.com/api-reference/tasks/comment-item/task-comment-item-get.html
     *
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'task.stages.get',
        'https://apidocs.bitrix24.com/api-reference/tasks/comment-item/task-comment-item-get.html',
        'Retrieves Kanban or "My Planner" stages'
    )]
    public function get(int $entityId, bool $isAdmin = false): StagesResult
    {
        return new StagesResult(
            $this->core->call(
                'task.stages.get',
                [
                    'entityId' => $entityId,
                    'isAdmin' => $isAdmin,
                ]
            )
        );
    }

    /**
     * Determines if the current user can move tasks in the specified object.
     *
     * @link https://apidocs.bitrix24.com/api-reference/tasks/comment-item/task-comment-item-get.html
     *
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'task.stages.canmovetask',
        'https://apidocs.bitrix24.com/api-reference/tasks/comment-item/task-comment-item-get.html',
        'Determines if the current user can move tasks in the specified object.'
    )]
    public function canMoveTask(int $entityId, string $entityType): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call(
                'task.stages.canmovetask',
                [
                    'entityId' => $entityId,
                    'entityType' => $entityType,
                ]
            )
        );
    }

    /**
     * Moves tasks from one stage to another.
     *
     * @link https://apidocs.bitrix24.com/api-reference/tasks/stages/task-stages-move-task.html
     *
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'task.stages.movetask',
        'https://apidocs.bitrix24.com/api-reference/tasks/stages/task-stages-move-task.html',
        'Moves tasks from one stage to another.'
    )]
    public function moveTask(int $id, int $stageId, ?int $before = null, ?int $after = null): UpdatedItemResult
    {
        $params = [
            'id' => $id,
            'stageId' => $stageId
        ];
        if ($before !== null && $before !== 0) {
            $params['before'] = $before;
        } elseif ($after !== null && $after !== 0) {
            $params['after'] = $after;
        }

        return new UpdatedItemResult(
            $this->core->call(
                'task.stages.movetask',
                $params
            )
        );
    }

}
