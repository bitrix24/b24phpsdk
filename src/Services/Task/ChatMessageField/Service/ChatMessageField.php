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

namespace Bitrix24\SDK\Services\Task\ChatMessageField\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Task\ChatMessageField\Result\ChatMessageFieldResult;
use Bitrix24\SDK\Services\Task\ChatMessageField\Result\ChatMessageFieldsResult;

#[ApiServiceMetadata(new Scope(['task']))]
class ChatMessageField extends AbstractService
{
    /**
     * Get metadata for a single task chat message field by field code.
     *
     * @link https://apidocs.bitrix24.ru/api-reference/rest-v3/tasks/tasks-task-chat-message-field-get.html
     *
     * @param non-empty-string $name   Field code, e.g. 'taskId'
     * @param string[]         $select Fields to return. Available: name, type, title, description,
     *                                 validationRules, requiredGroups, filterable, sortable,
     *                                 editable, multiple, elementType
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'tasks.task.chat.message.field.get',
        'https://apidocs.bitrix24.ru/api-reference/rest-v3/tasks/tasks-task-chat-message-field-get.html',
        'Get metadata for a single task chat message field by field code',
        ApiVersion::v3
    )]
    public function get(string $name, array $select = []): ChatMessageFieldResult
    {
        $this->guardNonEmptyString($name, 'field name must not be empty');

        $params = ['name' => $name];
        if ($select !== []) {
            $params['select'] = $select;
        }

        return new ChatMessageFieldResult(
            $this->core->call('tasks.task.chat.message.field.get', $params, ApiVersion::v3)
        );
    }

    /**
     * Get list of all available task chat message field descriptors.
     *
     * @link https://apidocs.bitrix24.ru/api-reference/rest-v3/tasks/tasks-task-chat-message-field-list.html
     *
     * @param string[] $select Fields to return. Available: name, type, title, description,
     *                         validationRules, requiredGroups, filterable, sortable,
     *                         editable, multiple, elementType
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'tasks.task.chat.message.field.list',
        'https://apidocs.bitrix24.ru/api-reference/rest-v3/tasks/tasks-task-chat-message-field-list.html',
        'Get list of all available task chat message field descriptors',
        ApiVersion::v3
    )]
    public function list(array $select = []): ChatMessageFieldsResult
    {
        $params = $select !== [] ? ['select' => $select] : [];

        return new ChatMessageFieldsResult(
            $this->core->call('tasks.task.chat.message.field.list', $params, ApiVersion::v3)
        );
    }
}
