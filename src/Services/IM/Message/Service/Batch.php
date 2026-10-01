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

namespace Bitrix24\SDK\Services\IM\Message\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AddedItemBatchResult;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Core\Result\UpdatedItemBatchResult;
use Bitrix24\SDK\Services\IM;
use Bitrix24\SDK\Services\IM\Message\Attach\Contracts\AttachPayloadInterface;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['im']))]
class Batch
{
    public function __construct(protected IM\Batch $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch send messages to chats
     *
     * @link https://apidocs.bitrix24.com/api-reference/chats/messages/im-message-add.html
     *
     * @param array<int, array{
     *   DIALOG_ID: string,
     *   MESSAGE?: string,
     *   ATTACH?: array<array-key, mixed>|string|AttachPayloadInterface,
     *   KEYBOARD?: array<array-key, mixed>|string,
     *   MENU?: array<array-key, mixed>|string,
     *   SYSTEM?: string,
     *   URL_PREVIEW?: string,
     *   REPLY_ID?: int,
     * }> $messages
     *
     * @return Generator<int, AddedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'im.message.add',
        'https://apidocs.bitrix24.com/api-reference/chats/messages/im-message-add.html',
        'Batch send messages to chats'
    )]
    public function add(array $messages): Generator
    {
        foreach ($this->batch->addEntityItems('im.message.add', $this->normalizeAttach($messages)) as $key => $item) {
            yield $key => new AddedItemBatchResult($item);
        }
    }

    /**
     * Batch update text and parameters of sent messages
     *
     * @link https://apidocs.bitrix24.com/api-reference/chats/messages/im-message-update.html
     *
     * @param array<int, array{
     *   MESSAGE_ID: int,
     *   MESSAGE?: string,
     *   ATTACH?: array<array-key, mixed>|string|AttachPayloadInterface,
     *   KEYBOARD?: array<array-key, mixed>|string,
     *   MENU?: array<array-key, mixed>|string,
     *   URL_PREVIEW?: string,
     *   IS_EDITED?: string,
     * }> $messages
     *
     * @return Generator<int, UpdatedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'im.message.update',
        'https://apidocs.bitrix24.com/api-reference/chats/messages/im-message-update.html',
        'Batch update text and parameters of sent messages'
    )]
    public function update(array $messages): Generator
    {
        foreach ($this->batch->processEntityItems('im.message.update', $this->normalizeAttach($messages)) as $key => $item) {
            yield $key => new UpdatedItemBatchResult($item);
        }
    }

    /**
     * Batch delete messages
     *
     * @link https://apidocs.bitrix24.com/api-reference/chats/messages/im-message-delete.html
     *
     * @param int[] $messageIds
     *
     * @return Generator<int, DeletedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'im.message.delete',
        'https://apidocs.bitrix24.com/api-reference/chats/messages/im-message-delete.html',
        'Batch delete messages'
    )]
    public function delete(array $messageIds): Generator
    {
        $commandsParameters = array_map(
            static fn (int $messageId): array => ['MESSAGE_ID' => $messageId],
            array_values($messageIds)
        );

        foreach ($this->batch->processEntityItems('im.message.delete', $commandsParameters) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }

    /**
     * Builds typed attachments into raw payloads accepted by the REST API
     *
     * @param array<int, array<string, mixed>> $messages
     *
     * @return array<int, array<string, mixed>>
     */
    private function normalizeAttach(array $messages): array
    {
        return array_map(
            static function (array $message): array {
                if (($message['ATTACH'] ?? null) instanceof AttachPayloadInterface) {
                    $message['ATTACH'] = $message['ATTACH']->build();
                }

                return $message;
            },
            $messages
        );
    }
}
