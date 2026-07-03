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

namespace Bitrix24\SDK\Tests\Integration\Services\IM\Dialog\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\IM\Chat\ChatType;
use Bitrix24\SDK\Services\IM\Chat\Service\Chat;
use Bitrix24\SDK\Services\IM\Dialog\Service\Dialog;
use Bitrix24\SDK\Services\IM\Message\Service\Message;
use Bitrix24\SDK\Tests\Integration\Fabric;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Dialog::class)]
final class DialogTest extends DialogChatTestCase
{
    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('im.dialog.get returns dialog information for a created chat')]
    public function testGet(): void
    {
        $chatId = $this->createChat();
        $dialogId = $this->createDialogId($chatId);

        $dialog = $this->dialogService->get($dialogId)->dialog();

        $this->assertNotNull($dialog);
        $this->assertSame($dialogId, $dialog->dialog_id);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('im.dialog.messages.get returns seeded dialog messages')]
    public function testMessagesGet(): void
    {
        $chatId = $this->createChat();
        $dialogId = $this->createDialogId($chatId);
        $messageIds = $this->seedMessages($dialogId, [
            sprintf('Dialog get seed %s', uniqid('', true)),
            sprintf('Dialog get seed %s', uniqid('', true)),
        ]);

        $dialogMessagesResult = $this->dialogService->messagesGet($dialogId, null, null, 10);

        $messageIdsFromResponse = array_map(
            static fn(object $message): int => $message->id,
            $dialogMessagesResult->messages()
        );

        $this->assertNotEmpty($messageIdsFromResponse);
        $this->assertContains($messageIds[0], $messageIdsFromResponse);
        $this->assertContains($messageIds[1], $messageIdsFromResponse);
        $this->assertSame($chatId, $dialogMessagesResult->chatId());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('im.dialog.messages.search returns messages matching the search text')]
    public function testMessagesSearch(): void
    {
        $chatId = $this->createChat();
        $dialogId = $this->createDialogId($chatId);
        $needle = sprintf('needle-%s', uniqid('', true));
        $dateFrom = CarbonImmutable::now()->subMinutes(5);
        $this->seedMessages($dialogId, [
            sprintf('prefix %s suffix', $needle),
        ]);
        $dateTo = CarbonImmutable::now()->addMinutes(5);

        $dialogMessageSearchResult = $this->dialogService->messagesSearch($chatId, $needle, $dateFrom, $dateTo, limit: 20);
        $texts = array_map(
            static fn(object $message): string => $message->text,
            $dialogMessageSearchResult->messages()
        );

        $this->assertNotEmpty($texts);
        $this->assertTrue(
            array_any(
                $texts,
                static fn(string $text): bool => str_contains($text, $needle)
            )
        );
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('im.dialog.read returns the updated read state')]
    public function testRead(): void
    {
        $chatId = $this->createChat();
        $dialogId = $this->createDialogId($chatId);
        $messageId = $this->seedMessages($dialogId, [
            sprintf('Read state %s', uniqid('', true)),
        ])[0];

        $this->assertTrue($this->dialogService->unread($dialogId, $messageId)->isSuccess());

        $readState = $this->dialogService->read($dialogId, $messageId)->readState();

        $this->assertNotNull($readState);
        $this->assertSame($dialogId, $readState->dialogId);
        $this->assertSame($chatId, $readState->chatId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('im.dialog.read.all returns success')]
    public function testReadAll(): void
    {
        $this->assertTrue($this->dialogService->readAll()->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('im.dialog.unread returns success for a seeded message')]
    public function testUnread(): void
    {
        $dialogId = $this->createDialogId($this->createChat());
        $messageId = $this->seedMessages($dialogId, [
            sprintf('Unread state %s', uniqid('', true)),
        ])[0];

        $this->assertTrue($this->dialogService->unread($dialogId, $messageId)->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('im.dialog.users.list returns participants and pagination metadata')]
    public function testUsersList(): void
    {
        $chatId = $this->createChat();
        $dialogId = $this->createDialogId($chatId);

        $dialogUsersResult = $this->dialogService->usersList($dialogId, limit: 50);
        $userIds = array_map(
            static fn(object $user): int => $user->id,
            $dialogUsersResult->users()
        );

        $this->assertNotEmpty($userIds);
        $this->assertContains($this->currentUserId, $userIds);
        $this->assertGreaterThanOrEqual(1, $dialogUsersResult->total());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('im.dialog.writing returns success for a dialog')]
    public function testWriting(): void
    {
        $dialogId = $this->createDialogId($this->createChat());

        $this->assertTrue($this->dialogService->writing($dialogId)->isSuccess());
    }
}
