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

namespace Bitrix24\SDK\Services\IM\Notify\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AddedItemBatchResult;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Core\Result\UpdatedItemBatchResult;
use Bitrix24\SDK\Services\IM;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['im']))]
class Batch
{
    /**
     * Count of notification IDs passed to a single im.notify.read.list command
     */
    private const NOTIFICATION_IDS_CHUNK_SIZE = 50;

    public function __construct(protected IM\Batch $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch send system notifications
     *
     * @link https://apidocs.bitrix24.com/api-reference/chats/notifications/im-notify-system-add.html
     *
     * @param array<int, array{
     *   USER_ID: int,
     *   MESSAGE: string,
     *   MESSAGE_OUT?: string,
     *   TAG?: string,
     *   SUB_TAG?: string,
     *   ATTACH?: array<array-key, mixed>,
     * }> $notifications
     *
     * @return Generator<int, AddedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'im.notify.system.add',
        'https://apidocs.bitrix24.com/api-reference/chats/notifications/im-notify-system-add.html',
        'Batch send system notifications'
    )]
    public function fromSystem(array $notifications): Generator
    {
        foreach ($this->batch->addEntityItems('im.notify.system.add', $notifications) as $key => $item) {
            yield $key => new AddedItemBatchResult($item);
        }
    }

    /**
     * Batch send personal notifications
     *
     * @link https://apidocs.bitrix24.com/api-reference/chats/notifications/im-notify-personal-add.html
     *
     * @param array<int, array{
     *   USER_ID: int,
     *   MESSAGE: string,
     *   MESSAGE_OUT?: string,
     *   TAG?: string,
     *   SUB_TAG?: string,
     *   ATTACH?: array<array-key, mixed>,
     * }> $notifications
     *
     * @return Generator<int, AddedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'im.notify.personal.add',
        'https://apidocs.bitrix24.com/api-reference/chats/notifications/im-notify-personal-add.html',
        'Batch send personal notifications'
    )]
    public function fromPersonal(array $notifications): Generator
    {
        foreach ($this->batch->addEntityItems('im.notify.personal.add', $notifications) as $key => $item) {
            yield $key => new AddedItemBatchResult($item);
        }
    }

    /**
     * Batch delete notifications
     *
     * @link https://apidocs.bitrix24.com/api-reference/chats/notifications/im-notify-delete.html
     *
     * @param int[] $notificationIds
     *
     * @return Generator<int, DeletedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'im.notify.delete',
        'https://apidocs.bitrix24.com/api-reference/chats/notifications/im-notify-delete.html',
        'Batch delete notifications'
    )]
    public function delete(array $notificationIds): Generator
    {
        $commandsParameters = array_map(
            static fn (int $notificationId): array => ['ID' => $notificationId],
            array_values($notificationIds)
        );

        foreach ($this->batch->processEntityItems('im.notify.delete', $commandsParameters) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }

    /**
     * Batch "read" the list of notifications, excluding CONFIRM notification type
     *
     * Notification IDs are split into chunks, one im.notify.read.list command is executed per chunk.
     *
     * @link https://apidocs.bitrix24.com/api-reference/chats/notifications/im-notify-read-list.html
     *
     * @param int[] $notificationIds
     *
     * @return Generator<int, UpdatedItemBatchResult> one result per chunk of notification IDs
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'im.notify.read.list',
        'https://apidocs.bitrix24.com/api-reference/chats/notifications/im-notify-read-list.html',
        'Batch "read" the list of notifications, excluding CONFIRM notification type'
    )]
    public function markMessagesAsRead(array $notificationIds): Generator
    {
        yield from $this->readList($notificationIds, 'Y');
    }

    /**
     * Batch "unread" the list of notifications, excluding CONFIRM notification type
     *
     * Notification IDs are split into chunks, one im.notify.read.list command is executed per chunk.
     *
     * @link https://apidocs.bitrix24.com/api-reference/chats/notifications/im-notify-read-list.html
     *
     * @param int[] $notificationIds
     *
     * @return Generator<int, UpdatedItemBatchResult> one result per chunk of notification IDs
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'im.notify.read.list',
        'https://apidocs.bitrix24.com/api-reference/chats/notifications/im-notify-read-list.html',
        'Batch "unread" the list of notifications, excluding CONFIRM notification type'
    )]
    public function markMessagesAsUnread(array $notificationIds): Generator
    {
        yield from $this->readList($notificationIds, 'N');
    }

    /**
     * @param int[] $notificationIds
     *
     * @return Generator<int, UpdatedItemBatchResult>
     * @throws BaseException
     */
    private function readList(array $notificationIds, string $action): Generator
    {
        $commandsParameters = array_map(
            static fn (array $chunk): array => [
                'IDS' => $chunk,
                'ACTION' => $action,
            ],
            array_chunk(array_values($notificationIds), self::NOTIFICATION_IDS_CHUNK_SIZE)
        );

        foreach ($this->batch->processEntityItems('im.notify.read.list', $commandsParameters) as $key => $item) {
            yield $key => new UpdatedItemBatchResult($item);
        }
    }
}
