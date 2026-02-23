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

#[ApiServiceMetadata(new Scope(['task']))]
class TaskChat extends AbstractService
{
    /**
     *Send a Message to Task Chat tasks.task.chat.message.send
     *
     * @link https://apidocs.bitrix24.com/api-reference/rest-v3/tasks/tasks-task-chat-message-send.html
     *
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'tasks.task.chat.message.send',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/tasks/tasks-task-chat-message-send.html',
        'Send Message to Task Chat tasks.task.chat.message.send',
        ApiVersion::v3
    )]
    /**
     * @param positive-int $taskId Task ID.
     * @param non-empty-string $message chat message.
     */
    public function sendMessage(int $taskId, string $message): MessageSentResult
    {
        return new MessageSentResult($this->core->call('tasks.task.chat.message.send', [
            'fields' => [
                'taskId' => $taskId,
                'text' => $message
            ]
        ], ApiVersion::v3));
    }
}
