<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
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
use Bitrix24\SDK\Services\Task\Result\TaskFieldResult;
use Bitrix24\SDK\Services\Task\Result\TaskFieldsResult;

#[ApiServiceMetadata(new Scope(['task']))]
class TaskField extends AbstractService
{
    /**
     * Returns task field metadata by field name.
     *
     * @link https://apidocs.bitrix24.com/api-reference/rest-v3/tasks/tasks-task-field-get.html
     *
     * @param non-empty-string $name field name.
     * @param array<int,string> $select
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'tasks.task.field.get',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/tasks/tasks-task-field-get.html',
        'Returns task field metadata by field name.',
        ApiVersion::v3
    )]
    public function get(string $name, array $select = []): TaskFieldResult
    {
        return new TaskFieldResult($this->core->call(
            'tasks.task.field.get',
            [
                'name' => $name,
                'select' => $select,
            ],
            ApiVersion::v3
        ));
    }

    /**
     * Returns available task field metadata.
     *
     * @link https://apidocs.bitrix24.com/api-reference/rest-v3/tasks/tasks-task-field-list.html
     *
     * @param array<int,string> $select
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'tasks.task.field.list',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/tasks/tasks-task-field-list.html',
        'Returns available task field metadata.',
        ApiVersion::v3
    )]
    public function list(array $select = []): TaskFieldsResult
    {
        return new TaskFieldsResult($this->core->call(
            'tasks.task.field.list',
            [
                'select' => $select,
            ],
            ApiVersion::v3
        ));
    }
}
