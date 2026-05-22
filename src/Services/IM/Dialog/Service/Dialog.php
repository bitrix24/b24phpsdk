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

namespace Bitrix24\SDK\Services\IM\Dialog\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\IM\Dialog\Result\DialogActionResult;
use Bitrix24\SDK\Services\IM\Dialog\Result\DialogMessageSearchResult;
use Bitrix24\SDK\Services\IM\Dialog\Result\DialogMessagesResult;
use Bitrix24\SDK\Services\IM\Dialog\Result\DialogReadResult;
use Bitrix24\SDK\Services\IM\Dialog\Result\DialogResult;
use Bitrix24\SDK\Services\IM\Dialog\Result\DialogUsersResult;
use Carbon\CarbonImmutable;

#[ApiServiceMetadata(new Scope(['im']))]
class Dialog extends AbstractService
{
    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.dialog.get',
        'https://apidocs.bitrix24.ru/api-reference/chats/im-dialog-get.html',
        'Get dialog information'
    )]
    public function get(string $dialogId): DialogResult
    {
        return new DialogResult($this->core->call('im.dialog.get', [
            'DIALOG_ID' => $dialogId,
        ]));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.dialog.messages.get',
        'https://apidocs.bitrix24.ru/api-reference/chats/messages/im-dialog-messages-get.html',
        'Get dialog messages'
    )]
    public function messagesGet(
        string $dialogId,
        ?int $lastId = null,
        ?int $firstId = null,
        ?int $limit = null,
    ): DialogMessagesResult {
        $payload = [
            'DIALOG_ID' => $dialogId,
            'LAST_ID' => $lastId,
            'FIRST_ID' => $firstId,
            'LIMIT' => $limit,
        ];

        return new DialogMessagesResult($this->core->call('im.dialog.messages.get', array_filter(
            $payload,
            static fn(mixed $value): bool => $value !== null
        )));
    }

    /**
     * @param array<string, string>|null $order
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.dialog.messages.search',
        'https://apidocs.bitrix24.com/api-reference/chats/messages/im-dialog-messages-search.html',
        'Search messages in a chat'
    )]
    public function messagesSearch(
        int $chatId,
        ?string $searchMessage = null,
        ?CarbonImmutable $dateFrom = null,
        ?CarbonImmutable $dateTo = null,
        ?CarbonImmutable $date = null,
        ?array $order = null,
        ?int $limit = null,
        ?int $lastId = null,
    ): DialogMessageSearchResult {
        $payload = [
            'CHAT_ID' => $chatId,
            'SEARCH_MESSAGE' => $searchMessage,
            'DATE_FROM' => $dateFrom?->toAtomString(),
            'DATE_TO' => $dateTo?->toAtomString(),
            'DATE' => $date?->toAtomString(),
            'ORDER' => $order,
            'LIMIT' => $limit,
            'LAST_ID' => $lastId,
        ];

        return new DialogMessageSearchResult($this->core->call('im.dialog.messages.search', array_filter(
            $payload,
            static fn(mixed $value): bool => $value !== null
        )));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.dialog.read',
        'https://apidocs.bitrix24.ru/api-reference/chats/messages/im-dialog-read.html',
        'Mark dialog messages as read'
    )]
    public function read(string $dialogId, ?int $messageId = null): DialogReadResult
    {
        $payload = ['DIALOG_ID' => $dialogId];
        if ($messageId !== null) {
            $payload['MESSAGE_ID'] = $messageId;
        }

        return new DialogReadResult($this->core->call('im.dialog.read', $payload));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.dialog.read.all',
        'https://apidocs.bitrix24.ru/api-reference/chats/special-operations/im-dialog-read-all.html',
        'Mark all dialogs as read'
    )]
    public function readAll(): DialogActionResult
    {
        return new DialogActionResult($this->core->call('im.dialog.read.all', []));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.dialog.unread',
        'https://apidocs.bitrix24.ru/api-reference/chats/messages/im-dialog-unread.html',
        'Mark dialog messages as unread'
    )]
    public function unread(string $dialogId, int $messageId): DialogActionResult
    {
        return new DialogActionResult($this->core->call('im.dialog.unread', [
            'DIALOG_ID' => $dialogId,
            'MESSAGE_ID' => $messageId,
        ]));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.dialog.users.list',
        'https://apidocs.bitrix24.ru/api-reference/chats/chat-users/im-dialog-users-list.html',
        'List dialog participants'
    )]
    public function usersList(
        string $dialogId,
        bool $skipExternal = false,
        ?string $skipExternalExceptTypes = null,
        ?int $limit = null,
        ?int $lastId = null,
        ?int $offset = null,
    ): DialogUsersResult {
        $payload = [
            'DIALOG_ID' => $dialogId,
            'SKIP_EXTERNAL' => $skipExternal ? 'Y' : 'N',
            'SKIP_EXTERNAL_EXCEPT_TYPES' => $skipExternalExceptTypes,
            'LIMIT' => $limit,
            'LAST_ID' => $lastId,
            'OFFSET' => $offset,
        ];

        return new DialogUsersResult($this->core->call('im.dialog.users.list', array_filter(
            $payload,
            static fn(mixed $value): bool => $value !== null
        )));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.dialog.writing',
        'https://apidocs.bitrix24.ru/api-reference/chats/messages/im-dialog-writing.html',
        'Send typing indicator'
    )]
    public function writing(string $dialogId): DialogActionResult
    {
        return new DialogActionResult($this->core->call('im.dialog.writing', [
            'DIALOG_ID' => $dialogId,
        ]));
    }
}
