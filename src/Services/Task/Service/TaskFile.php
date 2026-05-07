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
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Task\Result\FileAttachedToTaskResult;

#[ApiServiceMetadata(new Scope(['task']))]
class TaskFile extends AbstractService
{
    /**
     * Attach Exists Files to Task tasks.task.file.attach
     *
     * @link https://apidocs.bitrix24.com/api-reference/rest-v3/tasks/tasks-task-file-attach.html
     *
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'tasks.task.file.attach',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/tasks/tasks-task-file-attach.html',
        'Attach Exists Files to Task tasks.task.file.attach',
        ApiVersion::v3
    )]
    /**
     * @param positive-int $taskId Task ID.
     * @param array<non-negative-int, positive-int> $fileIds File IDs.
     */
    public function attachExists(int $taskId, array $fileIds): FileAttachedToTaskResult
    {
        return new FileAttachedToTaskResult($this->core->call('tasks.task.file.attach', [
            'taskId' => $taskId,
            'fileIds' => $fileIds
        ], ApiVersion::v3));
    }
}
