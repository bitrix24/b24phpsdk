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

namespace Bitrix24\SDK\Services\Task\AccessField\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Task\AccessField\Result\AccessFieldResult;
use Bitrix24\SDK\Services\Task\AccessField\Result\AccessFieldsResult;

#[ApiServiceMetadata(new Scope(['task']))]
class AccessField extends AbstractService
{
    /**
     * Get metadata for a single task access field by field code.
     *
     * @link https://apidocs.bitrix24.ru/api-reference/rest-v3/tasks/tasks-task-access-field-get.html
     *
     * @param non-empty-string $name   Field code, e.g. 'id'
     * @param string[]         $select Fields to return. Available: name, type, title, description,
     *                                 validationRules, requiredGroups, filterable, sortable,
     *                                 editable, multiple, elementType
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'tasks.task.access.field.get',
        'https://apidocs.bitrix24.ru/api-reference/rest-v3/tasks/tasks-task-access-field-get.html',
        'Get metadata for a single task access field by field code',
        ApiVersion::v3
    )]
    public function get(string $name, array $select = []): AccessFieldResult
    {
        $this->guardNonEmptyString($name, 'field name must not be empty');

        $params = ['name' => $name];
        if ($select !== []) {
            $params['select'] = $select;
        }

        return new AccessFieldResult(
            $this->core->call('tasks.task.access.field.get', $params, ApiVersion::v3)
        );
    }

    /**
     * Get list of all available task access field descriptors.
     *
     * @link https://apidocs.bitrix24.ru/api-reference/rest-v3/tasks/tasks-task-access-field-list.html
     *
     * @param string[] $select Fields to return. Available: name, type, title, description,
     *                         validationRules, requiredGroups, filterable, sortable,
     *                         editable, multiple, elementType
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'tasks.task.access.field.list',
        'https://apidocs.bitrix24.ru/api-reference/rest-v3/tasks/tasks-task-access-field-list.html',
        'Get list of all available task access field descriptors',
        ApiVersion::v3
    )]
    public function list(array $select = []): AccessFieldsResult
    {
        $params = $select !== [] ? ['select' => $select] : [];

        return new AccessFieldsResult(
            $this->core->call('tasks.task.access.field.list', $params, ApiVersion::v3)
        );
    }
}
