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

namespace Bitrix24\SDK\Services\Task\Checklistitem\Service;

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

use Bitrix24\SDK\Services\Task\Checklistitem\Result\ChecklistitemsResult;
use Bitrix24\SDK\Services\Task\Checklistitem\Result\ChecklistitemResult;
use Bitrix24\SDK\Services\Task\Checklistitem\Result\ManifestItemResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['task']))]
class Checklistitem extends AbstractService
{
    /**
     * Adds a new checklist item to the task
     *
     * @link https://apidocs.bitrix24.com/api-reference/tasks/checklist-item/task-checklist-item-add.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'task.checklistitem.add',
        'https://apidocs.bitrix24.com/api-reference/tasks/checklist-item/task-checklist-item-add.html',
        'Adds a new checklist item to the task'
    )]
    public function add(int $taskId, string $title, int $sort = 10, bool $completed = false): AddedItemResult
    {
        return new AddedItemResult(
            $this->core->call(
                'task.checklistitem.add',
                [
                    'TASKID' => $taskId,
                    'FIELDS' => [
                        'TITLE' => $title,
                        'SORT_INDEX' => $sort,
                        'IS_COMPLETE' => $completed
                    ]
                ]
            )
        );
    }

    /**
     * Deletes a checklist item.
     *
     * @link https://apidocs.bitrix24.com/api-reference/tasks/checklist-item/task-checklist-item-delete.html
     *
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'task.checklistitem.delete',
        'https://apidocs.bitrix24.com/api-reference/tasks/checklist-item/task-checklist-item-delete.html',
        'Deletes a checklist item.'
    )]
    public function delete(int $taskId, int $itemId): DeletedItemResult
    {
        return new DeletedItemResult(
            $this->core->call(
                'task.checklistitem.delete',
                [
                    'TASKID' => $taskId,
                    'ITEMID' => $itemId,
                ]
            )
        );
    }

    /**
     * Retrieves a checklist item by its id.
     *
     * @link https://apidocs.bitrix24.com/api-reference/tasks/checklist-item/task-checklist-item-get.html
     *
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'task.checklistitem.get',
        'https://apidocs.bitrix24.com/api-reference/tasks/checklist-item/task-checklist-item-get.html',
        'Retrieves a checklist item by its id'
    )]
    public function get(int $taskId, int $itemId): ChecklistitemResult
    {
        return new ChecklistitemResult(
            $this->core->call(
                'task.checklistitem.get',
                [
                    'TASKID' => $taskId,
                    'ITEMID' => $itemId,                        
                ]
            )
        );
    }

    /**
     * Retrieves a list of checklist items in the task.
     *
     * @link https://apidocs.bitrix24.com/api-reference/tasks/checklist-item/task-checklist-item-get-list.html
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
        'task.checklistitem.getlist',
        'https://apidocs.bitrix24.com/api-reference/tasks/checklist-item/task-checklist-item-get-list.html',
        'Retrieves a list of checklist items in the task.'
    )]
    public function getList(int $taskId, ?array $order = []): ChecklistitemsResult
    {
        return new ChecklistitemsResult(
            $this->core->call(
                'task.checklistitem.getlist',
                [
                    'TASKID' => $taskId,
                    'ORDER' => $order,       
                ]              
            )
        );
    }

    /**
     * Updates the data of a checklist item.
     *
     * @link https://apidocs.bitrix24.com/api-reference/tasks/checklist-item/task-checklist-item-update.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'task.checklistitem.update',
        'https://apidocs.bitrix24.com/api-reference/tasks/checklist-item/task-checklist-item-update.html',
        'Updates the data of a checklist item.'
    )]
    public function update(int $taskId, int $itemId, array $fields): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call(
                'task.checklistitem.update',
                [
                    'TASKID' => $taskId,
                    'ITEMID' => $itemId,  
                    'FIELDS' => $fields,  
                ]
            )
        );
    }
    
    /**
     * Moves a checklist item in the list after the specified one.
     *
     * @link https://apidocs.bitrix24.com/api-reference/tasks/checklist-item/task-checklist-item-move-after-item.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'task.checklistitem.moveafteritem',
        'https://apidocs.bitrix24.com/api-reference/tasks/checklist-item/task-checklist-item-move-after-item.html',
        'Moves a checklist item in the list after the specified one.'
    )]
    public function moveAfterItem(int $taskId, int $itemId, int $afterItemId): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call(
                'task.checklistitem.moveafteritem',
                [
                    'TASKID' => $taskId,
                    'ITEMID' => $itemId,  
                    'AFTERITEMID' => $afterItemId,  
                ]
            )
        );
    }
    
    /**
     * Marks a checklist item as completed.
     *
     * @link https://apidocs.bitrix24.com/api-reference/tasks/checklist-item/task-checklist-item-complete.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'task.checklistitem.complete',
        'https://apidocs.bitrix24.com/api-reference/tasks/checklist-item/task-checklist-item-complete.html',
        'Marks a checklist item as completed.'
    )]
    public function complete(int $taskId, int $itemId): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call(
                'task.checklistitem.complete',
                [
                    'TASKID' => $taskId,
                    'ITEMID' => $itemId,  
                ]
            )
        );
    }
    
    /**
     * Marks a completed checklist item as active again.
     *
     * @link https://apidocs.bitrix24.com/api-reference/tasks/checklist-item/task-checklist-item-renew.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'task.checklistitem.renew',
        'https://apidocs.bitrix24.com/api-reference/tasks/checklist-item/task-checklist-item-renew.html',
        'Marks a completed checklist item as active again.'
    )]
    public function renew(int $taskId, int $itemId): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call(
                'task.checklistitem.renew',
                [
                    'TASKID' => $taskId,
                    'ITEMID' => $itemId,  
                ]
            )
        );
    }
    
    /**
     * Checks if the action is allowed for the checklist item.
     * 
     * ActionId can be taken
     * 1 - ACTION_TIME_ADD
     * 2 - ACTION_MODIFY
     * 3 - ACTION_REMOVE
     * 4 - ACTION_TOGGLE
     *
     * @link https://apidocs.bitrix24.com/api-reference/tasks/checklist-item/task-checklist-item-is-action-allowed.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'task.checklistitem.isactionallowed',
        'https://apidocs.bitrix24.com/api-reference/tasks/checklist-item/task-checklist-item-is-action-allowed.html',
        'Checks if the action is allowed for the checklist item.'
    )]
    public function isActionAllowed(int $taskId, int $itemId, int $actionId): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call(
                'task.checklistitem.isactionallowed',
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
     * @link https://apidocs.bitrix24.com/api-reference/tasks/checklist-item/task-checklist-item-get-manifest.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'task.checklistitem.getmanifest',
        'https://apidocs.bitrix24.com/api-reference/tasks/checklist-item/task-checklist-item-get-manifest.html',
        'Retrieves a list of methods and their descriptions.'
    )]
    public function getManifest(): array
    {
        return $this->core->call('task.checklistitem.getmanifest',[])->getResponseData()->getResult();
    }

}
