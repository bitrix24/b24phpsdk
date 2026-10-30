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

namespace Bitrix24\SDK\Services\IM\Chat\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Core\Result\UpdatedItemBatchResult;
use Bitrix24\SDK\Services\IM;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['im']))]
class ChatUserBatch
{
    public function __construct(protected IM\Batch $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch add participants to chats
     *
     * @link https://apidocs.bitrix24.com/api-reference/chats/chat-users/im-chat-user-add.html
     *
     * @param array<int, array{
     *   CHAT_ID: int,
     *   USERS: int[],
     *   HIDE_HISTORY?: string,
     * }> $chatUsers
     *
     * @return Generator<int, UpdatedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'im.chat.user.add',
        'https://apidocs.bitrix24.com/api-reference/chats/chat-users/im-chat-user-add.html',
        'Batch add participants to chats'
    )]
    public function add(array $chatUsers): Generator
    {
        foreach ($this->batch->processEntityItems('im.chat.user.add', $chatUsers) as $key => $item) {
            yield $key => new UpdatedItemBatchResult($item);
        }
    }

    /**
     * Batch remove participants from chats
     *
     * @link https://apidocs.bitrix24.com/api-reference/chats/chat-users/im-chat-user-delete.html
     *
     * @param array<int, array{
     *   CHAT_ID: int,
     *   USER_ID: int,
     * }> $chatUsers
     *
     * @return Generator<int, DeletedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'im.chat.user.delete',
        'https://apidocs.bitrix24.com/api-reference/chats/chat-users/im-chat-user-delete.html',
        'Batch remove participants from chats'
    )]
    public function delete(array $chatUsers): Generator
    {
        foreach ($this->batch->processEntityItems('im.chat.user.delete', $chatUsers) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }

    /**
     * Batch list participant user IDs of several chats
     *
     * @link https://apidocs.bitrix24.com/api-reference/chats/chat-users/im-chat-user-list.html
     *
     * @param int[] $chatIds
     *
     * @return Generator<int, int[]> chat id => participant user IDs
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'im.chat.user.list',
        'https://apidocs.bitrix24.com/api-reference/chats/chat-users/im-chat-user-list.html',
        'Batch list participant user IDs of several chats'
    )]
    public function list(array $chatIds): Generator
    {
        $chatIds = array_values($chatIds);
        $commandsParameters = array_map(
            static fn (int $chatId): array => ['CHAT_ID' => $chatId],
            $chatIds
        );

        foreach ($this->batch->processEntityItems('im.chat.user.list', $commandsParameters) as $key => $item) {
            yield $chatIds[$key] => array_map(
                static fn (mixed $userId): int => (int)$userId,
                array_values($item->getResult())
            );
        }
    }
}
