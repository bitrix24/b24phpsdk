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

namespace Bitrix24\SDK\Services\IMBot\ChatMessage\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Core\Result\UpdatedItemBatchResult;
use Bitrix24\SDK\Services\IMBot\ChatMessage\Result\ChatMessageSentBatchResult;
use Generator;
use Psr\Log\LoggerInterface;

/**
 * Batch service for imbot.v2.Chat.Message.* methods.
 *
 * @see https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/messages/
 */
#[ApiBatchServiceMetadata(new Scope(['imbot']))]
class Batch
{
    public function __construct(
        protected readonly BatchOperationsInterface $batch,
        protected readonly LoggerInterface $log
    ) {
    }

    /**
     * Batch send messages on behalf of a bot.
     *
     * Each element of $messages must contain:
     *   - botId:    int    — bot identifier
     *   - dialogId: string — dialog or user ID to send to
     *   - fields:   array  — message fields (message, attach, keyboard, …)
     *
     * @param array<int, array{botId: int, dialogId: string, fields: array<string, mixed>}> $messages
     *
     * @return Generator<int, ChatMessageSentBatchResult>
     *
     * @throws BaseException
     *
     * @see https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/messages/chat-message-send.html
     */
    #[ApiBatchMethodMetadata(
        'imbot.v2.Chat.Message.send',
        'https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/messages/chat-message-send.html',
        'Batch send messages on behalf of a bot'
    )]
    public function send(array $messages): Generator
    {
        foreach ($this->batch->addEntityItems('imbot.v2.Chat.Message.send', $messages) as $key => $item) {
            yield $key => new ChatMessageSentBatchResult($item);
        }
    }

    /**
     * Batch delete messages sent by a bot.
     *
     * Each element of $messages must contain:
     *   - botId:     int — bot identifier
     *   - messageId: int — message ID to delete
     *
     * @param array<int, array{botId: int, messageId: int}> $messages
     *
     * @return Generator<int, DeletedItemBatchResult>
     *
     * @throws BaseException
     *
     * @see https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/messages/chat-message-delete.html
     */
    #[ApiBatchMethodMetadata(
        'imbot.v2.Chat.Message.delete',
        'https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/messages/chat-message-delete.html',
        'Batch delete messages sent by a bot'
    )]
    public function delete(array $messages): Generator
    {
        foreach ($this->batch->addEntityItems('imbot.v2.Chat.Message.delete', $messages) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }

    /**
     * Batch update messages sent by a bot.
     *
     * Each element of $messages must contain:
     *   - botId:     int   — bot identifier
     *   - messageId: int   — message ID to update
     *   - fields:    array — fields to update (message, attach, keyboard, urlPreview, isEdited)
     *
     * @param array<int, array{botId: int, messageId: int, fields: array<string, mixed>}> $messages
     *
     * @return Generator<int, UpdatedItemBatchResult>
     *
     * @throws BaseException
     *
     * @see https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/messages/chat-message-update.html
     */
    #[ApiBatchMethodMetadata(
        'imbot.v2.Chat.Message.update',
        'https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/messages/chat-message-update.html',
        'Batch update messages sent by a bot'
    )]
    public function update(array $messages): Generator
    {
        foreach ($this->batch->addEntityItems('imbot.v2.Chat.Message.update', $messages) as $key => $item) {
            yield $key => new UpdatedItemBatchResult($item);
        }
    }
}
