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

namespace Bitrix24\SDK\Services\IM\Notify\Service;


use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\AddedItemResult;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\IM\Notify\Result\NotifiesResult;
use Bitrix24\SDK\Services\IM\Notify\Result\NotifyHistorySearchResult;
use Bitrix24\SDK\Services\IM\Notify\Result\NotifyReadAllResult;
use Bitrix24\SDK\Services\IM\Notify\Result\NotifySchemaResult;
use Carbon\CarbonImmutable;

#[ApiServiceMetadata(new Scope(['im']))]
class Notify extends AbstractService
{
    /**
     * @param positive-int $userId
     * @param non-empty-string $message
     * @param non-empty-string|null $forEmailChannelMessage
     * @param non-empty-string|null $notificationTag
     * @param non-empty-string|null $subTag
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.notify.system.add',
        'https://apidocs.bitrix24.com/api-reference/chats/notifications/im-notify-system-add.html',
        'Sending system notification'
    )]
    public function fromSystem(
        int     $userId,
        string  $message,
        ?string $forEmailChannelMessage = null,
        ?string $notificationTag = null,
        ?string $subTag = null,
        ?array  $attachment = null
    ): AddedItemResult
    {
        return new AddedItemResult($this->core->call(
            'im.notify.system.add',
            [
                'USER_ID' => $userId,
                'MESSAGE' => $message,
                'MESSAGE_OUT' => $forEmailChannelMessage,
                'TAG' => $notificationTag,
                'SUB_TAG' => $subTag,
                'ATTACH' => $attachment,
            ]
        ));
    }

    /**
     * @param positive-int $userId
     * @param non-empty-string $message
     * @param non-empty-string|null $forEmailChannelMessage
     * @param non-empty-string|null $notificationTag
     * @param non-empty-string|null $subTag
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.notify.personal.add',
        'https://apidocs.bitrix24.com/api-reference/chats/notifications/im-notify-personal-add.html',
        'Sending personal notification'
    )]
    public function fromPersonal(
        int     $userId,
        string  $message,
        ?string $forEmailChannelMessage = null,
        ?string $notificationTag = null,
        ?string $subTag = null,
        ?array  $attachment = null
    ): AddedItemResult
    {
        return new AddedItemResult($this->core->call(
            'im.notify.personal.add',
            [
                'USER_ID' => $userId,
                'MESSAGE' => $message,
                'MESSAGE_OUT' => $forEmailChannelMessage,
                'TAG' => $notificationTag,
                'SUB_TAG' => $subTag,
                'ATTACH' => $attachment,
            ]
        ));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.notify',
        'https://apidocs.bitrix24.com/api-reference/chats/notifications/im-notify.html',
        'Send a notification from application context'
    )]
    public function send(
        int     $userId,
        string  $message,
        string  $type = 'USER',
        ?string $forEmailChannelMessage = null,
        ?string $notificationTag = null,
        ?string $subTag = null,
        ?array  $attachment = null
    ): AddedItemResult
    {
        return new AddedItemResult($this->core->call(
            'im.notify',
            [
                'USER_ID' => $userId,
                'MESSAGE' => $message,
                'TYPE' => $type,
                'MESSAGE_OUT' => $forEmailChannelMessage,
                'TAG' => $notificationTag,
                'SUB_TAG' => $subTag,
                'ATTACH' => $attachment,
            ]
        ));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.notify.delete',
        'https://apidocs.bitrix24.com/api-reference/chats/notifications/im-notify-delete.html',
        'Deleting notification'
    )]
    public function delete(
        int     $notificationId,
        ?string $notificationTag = null,
        ?string $subTag = null,
    ): DeletedItemResult
    {
        return new DeletedItemResult($this->core->call(
            'im.notify.delete',
            [
                'ID' => $notificationId,
                'TAG' => $notificationTag,
                'SUB_TAG' => $subTag
            ]
        ));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.notify.read',
        'https://apidocs.bitrix24.com/api-reference/chats/notifications/im-notify-read.html',
        'The method cancels notification for read messages.'
    )]
    public function markAsRead(
        int  $notificationId,
        bool $isOnlyCurrent = true,
    ): UpdatedItemResult
    {
        return new UpdatedItemResult($this->core->call(
            'im.notify.read',
            [
                'ID' => $notificationId,
                'ONLY_CURRENT' => $isOnlyCurrent ? 'Y' : 'N',
            ]
        ));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.notify.read.list',
        'https://apidocs.bitrix24.com/api-reference/chats/notifications/im-notify-read-list.html',
        '"Read" the list of notifications, excluding CONFIRM notification type'
    )]
    public function markMessagesAsRead(
        array $notificationIds
    ): UpdatedItemResult
    {
        return new UpdatedItemResult($this->core->call(
            'im.notify.read.list',
            [
                'IDS' => $notificationIds,
                'ACTION' => 'Y',
            ]
        ));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.notify.read.list',
        'https://apidocs.bitrix24.com/api-reference/chats/notifications/im-notify-read-list.html',
        '"Unread" the list of notifications, excluding CONFIRM notification type'
    )]
    public function markMessagesAsUnread(
        array $notificationIds
    ): UpdatedItemResult
    {
        return new UpdatedItemResult($this->core->call(
            'im.notify.read.list',
            [
                'IDS' => $notificationIds,
                'ACTION' => 'N',
            ]
        ));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.notify.read.all',
        'https://apidocs.bitrix24.com/api-reference/chats/notifications/im-notify-read-all.html',
        'Mark all notifications as read'
    )]
    public function markAllAsRead(): NotifyReadAllResult
    {
        return new NotifyReadAllResult($this->core->call('im.notify.read.all', []));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.notify.confirm',
        'https://apidocs.bitrix24.com/api-reference/chats/notifications/im-notify-confirm.html',
        'Interaction with notification buttons'
    )]
    public function confirm(
        int  $notificationId,
        bool $isAccept
    ): UpdatedItemResult
    {
        return new UpdatedItemResult($this->core->call(
            'im.notify.confirm',
            [
                'ID' => $notificationId,
                'NOTIFY_VALUE' => $isAccept ? 'Y' : 'N',
            ]
        ));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.notify.answer',
        'https://apidocs.bitrix24.com/api-reference/chats/notifications/im-notify-answer.html',
        'Response to notification, supporting quick reply'
    )]
    public function answer(
        int    $notificationId,
        string $answerText
    ): UpdatedItemResult
    {
        return new UpdatedItemResult($this->core->call(
            'im.notify.answer',
            [
                'ID' => $notificationId,
                'ANSWER_TEXT' => $answerText,
            ]
        ));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.notify.get',
        'https://apidocs.bitrix24.com/api-reference/chats/notifications/im-notify-get.html',
        'Get list of user notifications paginated by LAST_ID and LAST_TYPE'
    )]
    public function getList(
        ?int $lastId = null,
        ?int $lastType = null,
        int  $limit = 50,
    ): NotifiesResult
    {
        $params = array_filter(
            [
                'LAST_ID' => $lastId,
                'LAST_TYPE' => $lastType,
                'LIMIT' => $limit,
            ],
            static fn(mixed $value): bool => $value !== null
        );
        $params['LIMIT'] = $limit;

        return new NotifiesResult($this->core->call('im.notify.get', $params));
    }

    /**
     * @param non-empty-string[]|null $searchTypes
     * @param positive-int[]|null $searchAuthors
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.notify.history.search',
        'https://apidocs.bitrix24.com/api-reference/chats/notifications/im-notify-history-search.html',
        'Search notification history'
    )]
    public function historySearch(
        ?string          $searchText = null,
        ?array           $searchTypes = null,
        ?CarbonImmutable $searchDateFrom = null,
        ?CarbonImmutable $searchDateTo = null,
        ?array           $searchAuthors = null,
        ?int             $lastId = null,
        int              $limit = 50,
    ): NotifyHistorySearchResult
    {
        $params = array_filter(
            [
                'SEARCH_TEXT' => $searchText,
                'SEARCH_TYPES' => $searchTypes,
                'SEARCH_DATE_FROM' => $searchDateFrom?->toIso8601String(),
                'SEARCH_DATE_TO' => $searchDateTo?->toIso8601String(),
                'SEARCH_AUTHORS' => $searchAuthors,
                'LAST_ID' => $lastId,
                'LIMIT' => $limit,
            ],
            static fn(mixed $value): bool => $value !== null
        );
        $params['LIMIT'] = $limit;

        return new NotifyHistorySearchResult($this->core->call('im.notify.history.search', $params));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.notify.schema.get',
        'https://apidocs.bitrix24.com/api-reference/chats/notifications/im-notify-schema-get.html',
        'Get schema of available notification types per module'
    )]
    public function getSchema(): NotifySchemaResult
    {
        return new NotifySchemaResult($this->core->call('im.notify.schema.get', []));
    }
}
