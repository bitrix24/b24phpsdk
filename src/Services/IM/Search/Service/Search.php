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

namespace Bitrix24\SDK\Services\IM\Search\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\IM\Search\Result\SearchChatsResult;
use Bitrix24\SDK\Services\IM\Search\Result\SearchDepartmentsResult;
use Bitrix24\SDK\Services\IM\Search\Result\SearchLastItemsResult;
use Bitrix24\SDK\Services\IM\Search\Result\SearchUsersResult;

#[ApiServiceMetadata(new Scope(['im']))]
class Search extends AbstractService
{
    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.search.chat.list',
        'https://apidocs.bitrix24.com/api-reference/chats/search/im-search-chat-list.html',
        'Search chats available to the current user'
    )]
    public function chatList(
        ?string $find = null,
        ?string $findLines = null,
        ?int $offset = null,
        ?int $limit = null,
    ): SearchChatsResult {
        $payload = [
            'FIND' => $find,
            'FIND_LINES' => $findLines,
            'OFFSET' => $offset,
            'LIMIT' => $limit,
        ];

        return new SearchChatsResult($this->core->call('im.search.chat.list', array_filter(
            $payload,
            static fn(mixed $value): bool => $value !== null
        )));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.search.user.list',
        'https://apidocs.bitrix24.com/api-reference/chats/search/im-search-user-list.html',
        'Search users by name'
    )]
    public function userList(string $find, ?int $offset = null, ?int $limit = null): SearchUsersResult
    {
        $payload = [
            'FIND' => $find,
            'OFFSET' => $offset,
            'LIMIT' => $limit,
        ];

        return new SearchUsersResult($this->core->call('im.search.user.list', array_filter(
            $payload,
            static fn(mixed $value): bool => $value !== null
        )));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.search.department.list',
        'https://apidocs.bitrix24.com/api-reference/chats/search/im-search-department-list.html',
        'Search departments by full name'
    )]
    public function departmentList(
        string $find,
        bool $userData = false,
        ?int $offset = null,
        ?int $limit = null,
    ): SearchDepartmentsResult {
        $payload = [
            'FIND' => $find,
            'USER_DATA' => $userData ? 'Y' : 'N',
            'OFFSET' => $offset,
            'LIMIT' => $limit,
        ];

        return new SearchDepartmentsResult($this->core->call('im.search.department.list', array_filter(
            $payload,
            static fn(mixed $value): bool => $value !== null
        )));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     * @deprecated Developed for the previous chat UI; results are not shown in the current M1 chat interface.
     */
    #[ApiEndpointMetadata(
        'im.search.last.add',
        'https://apidocs.bitrix24.com/api-reference/chats/search/im-search-last-add.html',
        'Add a dialog to the legacy last search history',
        isDeprecated: true,
        deprecationMessage: 'Developed for the previous chat UI; results are not shown in the current M1 chat interface.'
    )]
    public function lastAdd(string $dialogId): UpdatedItemResult
    {
        return new UpdatedItemResult($this->core->call('im.search.last.add', [
            'DIALOG_ID' => $dialogId,
        ]));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     * @deprecated Developed for the previous chat UI; results are not shown in the current M1 chat interface.
     */
    #[ApiEndpointMetadata(
        'im.search.last.get',
        'https://apidocs.bitrix24.com/api-reference/chats/search/im-search-last-get.html',
        'Get legacy last search history',
        isDeprecated: true,
        deprecationMessage: 'Developed for the previous chat UI; results are not shown in the current M1 chat interface.'
    )]
    public function lastGet(
        bool $skipOpenLines = false,
        bool $skipChat = false,
        bool $skipDialog = false,
    ): SearchLastItemsResult {
        return new SearchLastItemsResult($this->core->call('im.search.last.get', [
            'SKIP_OPENLINES' => $skipOpenLines ? 'Y' : 'N',
            'SKIP_CHAT' => $skipChat ? 'Y' : 'N',
            'SKIP_DIALOG' => $skipDialog ? 'Y' : 'N',
        ]));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     * @deprecated Developed for the previous chat UI; results are not shown in the current M1 chat interface.
     */
    #[ApiEndpointMetadata(
        'im.search.last.delete',
        'https://apidocs.bitrix24.com/api-reference/chats/search/im-search-last-delete.html',
        'Delete a dialog from the legacy last search history',
        isDeprecated: true,
        deprecationMessage: 'Developed for the previous chat UI; results are not shown in the current M1 chat interface.'
    )]
    public function lastDelete(string $dialogId): UpdatedItemResult
    {
        return new UpdatedItemResult($this->core->call('im.search.last.delete', [
            'DIALOG_ID' => $dialogId,
        ]));
    }
}
