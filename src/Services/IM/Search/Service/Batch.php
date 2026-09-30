<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <algonexys@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\IM\Search\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Core\Result\UpdatedItemBatchResult;
use Bitrix24\SDK\Services\IM;
use Bitrix24\SDK\Services\IM\Search\Result\SearchChatItemResult;
use Bitrix24\SDK\Services\IM\Search\Result\SearchDepartmentItemResult;
use Bitrix24\SDK\Services\IM\Search\Result\SearchUserItemResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['im']))]
class Batch
{
    public function __construct(protected IM\Batch $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch search chats available to the current user
     *
     * @link https://apidocs.bitrix24.com/api-reference/chats/search/im-search-chat-list.html
     *
     * @param string|null $find search phrase for chats, at least 2 characters
     * @param string|null $findLines search phrase for Open Channels chats, at least 2 characters
     *
     * @return Generator<int, SearchChatItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'im.search.chat.list',
        'https://apidocs.bitrix24.com/api-reference/chats/search/im-search-chat-list.html',
        'Batch search chats available to the current user'
    )]
    public function chatList(?string $find = null, ?string $findLines = null, ?int $limit = null): Generator
    {
        $this->log->debug(
            'batchChatList',
            [
                'find' => $find,
                'findLines' => $findLines,
                'limit' => $limit,
            ]
        );

        $chats = $this->batch->getTraversableListByOffset(
            'im.search.chat.list',
            [
                'FIND' => $find,
                'FIND_LINES' => $findLines,
            ],
            $limit
        );
        foreach ($chats as $key => $value) {
            yield $key => new SearchChatItemResult($value);
        }
    }

    /**
     * Batch search users by name
     *
     * @link https://apidocs.bitrix24.com/api-reference/chats/search/im-search-user-list.html
     *
     * @param string $find search phrase, at least 2 characters
     *
     * @return Generator<int, SearchUserItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'im.search.user.list',
        'https://apidocs.bitrix24.com/api-reference/chats/search/im-search-user-list.html',
        'Batch search users by name'
    )]
    public function userList(string $find, ?int $limit = null): Generator
    {
        $this->log->debug(
            'batchUserList',
            [
                'find' => $find,
                'limit' => $limit,
            ]
        );

        $users = $this->batch->getTraversableListByOffset(
            'im.search.user.list',
            ['FIND' => $find],
            $limit
        );
        foreach ($users as $key => $value) {
            yield $key => new SearchUserItemResult($value);
        }
    }

    /**
     * Batch search departments by full name
     *
     * @link https://apidocs.bitrix24.com/api-reference/chats/search/im-search-department-list.html
     *
     * @param string $find search phrase by the beginning of words in the full department name
     *
     * @return Generator<int, SearchDepartmentItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'im.search.department.list',
        'https://apidocs.bitrix24.com/api-reference/chats/search/im-search-department-list.html',
        'Batch search departments by full name'
    )]
    public function departmentList(string $find, bool $userData = false, ?int $limit = null): Generator
    {
        $this->log->debug(
            'batchDepartmentList',
            [
                'find' => $find,
                'userData' => $userData,
                'limit' => $limit,
            ]
        );

        $departments = $this->batch->getTraversableListByOffset(
            'im.search.department.list',
            [
                'FIND' => $find,
                'USER_DATA' => $userData ? 'Y' : 'N',
            ],
            $limit
        );
        foreach ($departments as $key => $value) {
            yield $key => new SearchDepartmentItemResult($value);
        }
    }

    /**
     * Batch add dialogs to the legacy last search history
     *
     * @link https://apidocs.bitrix24.com/api-reference/chats/search/im-search-last-add.html
     *
     * @param string[] $dialogIds
     *
     * @return Generator<int, UpdatedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'im.search.last.add',
        'https://apidocs.bitrix24.com/api-reference/chats/search/im-search-last-add.html',
        'Batch add dialogs to the legacy last search history',
        isDeprecated: true,
        deprecationMessage: 'Developed for the previous chat UI; results are not shown in the current M1 chat interface.'
    )]
    #[\Deprecated(message: 'Developed for the previous chat UI; results are not shown in the current M1 chat interface.')]
    public function lastAdd(array $dialogIds): Generator
    {
        foreach ($this->batch->processEntityItems('im.search.last.add', $this->buildDialogCommands($dialogIds)) as $key => $item) {
            yield $key => new UpdatedItemBatchResult($item);
        }
    }

    /**
     * Batch delete dialogs from the legacy last search history
     *
     * @link https://apidocs.bitrix24.com/api-reference/chats/search/im-search-last-delete.html
     *
     * @param string[] $dialogIds
     *
     * @return Generator<int, DeletedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'im.search.last.delete',
        'https://apidocs.bitrix24.com/api-reference/chats/search/im-search-last-delete.html',
        'Batch delete dialogs from the legacy last search history',
        isDeprecated: true,
        deprecationMessage: 'Developed for the previous chat UI; results are not shown in the current M1 chat interface.'
    )]
    #[\Deprecated(message: 'Developed for the previous chat UI; results are not shown in the current M1 chat interface.')]
    public function lastDelete(array $dialogIds): Generator
    {
        foreach ($this->batch->processEntityItems('im.search.last.delete', $this->buildDialogCommands($dialogIds)) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }

    /**
     * @param string[] $dialogIds
     *
     * @return array<int, array{DIALOG_ID: string}>
     */
    private function buildDialogCommands(array $dialogIds): array
    {
        return array_map(
            static fn (string $dialogId): array => ['DIALOG_ID' => $dialogId],
            array_values($dialogIds)
        );
    }
}
