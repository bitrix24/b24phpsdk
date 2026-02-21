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
use Bitrix24\SDK\Core\Result\MessageSentResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Task\Result\AccessesResult;

#[ApiServiceMetadata(new Scope(['task']))]
class TaskAccess extends AbstractService
{
    /**
     * The method tasks.task.access.get checks the available actions a user can perform on a task.
     *
     * @link https://apidocs.bitrix24.com/api-reference/rest-v3/tasks/tasks-task-access-get.html
     *
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'tasks.task.access.get',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/tasks/tasks-task-access-get.html',
        'The method tasks.task.access.get checks the available actions a user can perform on a task.',
        ApiVersion::v3
    )]
    /**
     * @param positive-int $taskId Task ID.
     */
    public function get(int $taskId): AccessesResult
    {
        return new AccessesResult($this->core->call('tasks.task.access.get', [
            'id' => $taskId,
        ], ApiVersion::v3));
    }
}
