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

namespace Bitrix24\SDK\Services\Task\Flow\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Services\Task\Flow\Result\AddedFlowResult;
use Bitrix24\SDK\Services\Task\Flow\Result\UpdatedFlowResult;
use Bitrix24\SDK\Services\Task\Flow\Result\DeletedFlowResult;
use Bitrix24\SDK\Services\Task\Flow\Result\IsExistsFlowResult;
use Bitrix24\SDK\Services\Task\Flow\Result\FlowsResult;
use Bitrix24\SDK\Services\Task\Flow\Result\FlowResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['task']))]
class Flow extends AbstractService
{
    /**
     * Creates a flow
     *
     * @link https://apidocs.bitrix24.com/api-reference/tasks/flow/tasks-flow-flow-create.html
     *
     * $param array {
     *  name,
     *  description,
     *  groupId,
     *  ownerId,
     *  templateId,
     *  plannedCompletionTime,
     *  distributionType,
     *  responsibleList,
     *  taskCreators,
     *  matchWorkTime,
     *  responsibleCanChangeDeadline,
     *  notifyAtHalfTime,
     *  taskControl,
     *  notifyOnQueueOverflow,
     *  notifyOnTasksInProgressOverflow,
     *  notifyWhenEfficiencyDecreases,
     *  } $flowData
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'tasks.flow.Flow.create',
        'https://apidocs.bitrix24.com/api-reference/tasks/flow/tasks-flow-flow-create.html',
        'Creates a flow'
    )]
    public function create(array $flowData): AddedFlowResult
    {
        return new AddedFlowResult(
            $this->core->call(
                'tasks.flow.Flow.create',
                [
                    'flowData' => $flowData,
                ]
            )
        );
    }

    /**
     * Updates a flow.
     *
     * @link https://apidocs.bitrix24.com/api-reference/tasks/flow/tasks-flow-flow-update.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'tasks.flow.Flow.update',
        'https://apidocs.bitrix24.com/api-reference/tasks/flow/tasks-flow-flow-update.html',
        'Updates a flow.'
    )]
    public function update(array $flowData): UpdatedFlowResult
    {
        return new UpdatedFlowResult(
            $this->core->call(
                'tasks.flow.Flow.update',
                [
                    'flowData' => $flowData,
                ]
            )
        );
    }

    /**
     * Deletes a flow.
     *
     * @link https://apidocs.bitrix24.com/api-reference/tasks/flow/tasks-flow-flow-delete.html
     *
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'tasks.flow.Flow.delete',
        'https://apidocs.bitrix24.com/api-reference/tasks/flow/tasks-flow-flow-delete.html',
        'Deletes a flow.'
    )]
    public function delete(array $flowData): DeletedFlowResult
    {
        return new DeletedFlowResult(
            $this->core->call(
                'tasks.flow.Flow.delete',
                [
                    'flowData' => $flowData,
                ]
            )
        );
    }

    /**
     * Retrieves a flow.
     *
     * @link https://apidocs.bitrix24.com/api-reference/tasks/flow/tasks-flow-flow-get.html
     *
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'tasks.flow.Flow.get',
        'https://apidocs.bitrix24.com/api-reference/tasks/flow/tasks-flow-flow-get.html',
        'Retrieves a flow'
    )]
    public function get(int $flowId): FlowResult
    {
        return new FlowResult(
            $this->core->call(
                'tasks.flow.Flow.get',
                [
                    'flowId' => $flowId,
                ]
            )
        );
    }

    /**
     * Checks if a flow with that name exists.
     *
     * @link https://apidocs.bitrix24.com/api-reference/tasks/comment-item/task-comment-item-get.html
     *
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'tasks.flow.Flow.isExists',
        'https://apidocs.bitrix24.com/api-reference/tasks/comment-item/task-comment-item-get.html',
        'Checks if a flow with that name exists.'
    )]
    public function isExists(array $flowData): IsExistsFlowResult
    {
        return new IsExistsFlowResult(
            $this->core->call(
                'tasks.flow.Flow.isExists',
                [
                    'flowData' => $flowData,
                ]
            )
        );
    }

    /**
     * Enables or disables a flow.
     *
     * @link https://apidocs.bitrix24.com/api-reference/tasks/flow/tasks-flow-flow-activate.html
     *
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'tasks.flow.Flow.activate',
        'https://apidocs.bitrix24.com/api-reference/tasks/flow/tasks-flow-flow-activate.html',
        'Enables or disables a flow.'
    )]
    public function activate(int $flowId): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call(
                'tasks.flow.Flow.activate',
                [
                    'flowId' => $flowId,
                ]
            )
        );
    }

    /**
     * Pins or unpins a flow in the list.
     *
     * @link https://apidocs.bitrix24.com/api-reference/tasks/flow/tasks-flow-flow-pin.html
     *
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'tasks.flow.Flow.pin',
        'https://apidocs.bitrix24.com/api-reference/tasks/flow/tasks-flow-flow-pin.html',
        'Pins or unpins a flow in the list.'
    )]
    public function pin(int $flowId): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call(
                'tasks.flow.Flow.pin',
                [
                    'flowId' => $flowId,
                ]
            )
        );
    }

}
