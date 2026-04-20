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

namespace Bitrix24\SDK\Tests\Integration\Services\IM\Chat\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\IM\Chat\ChatColor;
use Bitrix24\SDK\Services\IM\Chat\ChatEntityType;
use Bitrix24\SDK\Services\IM\Chat\ChatType;
use Bitrix24\SDK\Services\IM\Chat\Service\Chat;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Chat::class)]
class ChatTest extends TestCase
{
    private Chat $chatService;

    private int $currentUserId = 0;

    /**
     * @var list<int>
     */
    private array $createdChats = [];

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->chatService = Factory::getServiceBuilder()->getIMScope()->chat();
        $this->currentUserId = (int)$this->chatService->core
            ->call('PROFILE')->getResponseData()->getResult()['ID'];
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function tearDown(): void
    {
        foreach ($this->createdChats as $createdChat) {
            try {
                $this->chatService->leave($createdChat);
            } catch (BaseException) {
                // chat may already be left by the test itself
            }
        }

        $this->createdChats = [];
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('im.chat.add returns a positive chat id')]
    public function testAdd(): void
    {
        $chatId = $this->createChat();
        $this->assertGreaterThan(0, $chatId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('im.chat.get returns the chat id matched by entity type and id')]
    public function testGet(): void
    {
        $entityId = sprintf('GET_%s', uniqid('', true));
        $chatId = $this->createChat(chatEntityType: ChatEntityType::Calendar, entityId: $entityId);

        $result = $this->chatService->get(ChatEntityType::Calendar, $entityId)->chat();

        $this->assertNotNull($result);
        $this->assertSame($chatId, $result->ID);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('im.chat.get returns null when no chat matches the entity')]
    public function testGetReturnsNullForUnknownEntity(): void
    {
        $entityId = sprintf('MISSING_%s', uniqid('', true));

        $result = $this->chatService->get(ChatEntityType::Calendar, $entityId)->chat();

        $this->assertNull($result);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('im.chat.leave returns success for the current user')]
    public function testLeave(): void
    {
        $chatId = $this->createChat();

        $updatedItemResult = $this->chatService->leave($chatId);

        $this->assertTrue($updatedItemResult->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('im.chat.mute toggles notifications by chat id')]
    public function testMute(): void
    {
        $chatId = $this->createChat();

        $this->assertTrue($this->chatService->mute($chatId, true)->isSuccess());
        $this->assertTrue($this->chatService->mute($chatId, false)->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('im.chat.mute toggles notifications by dialog id')]
    public function testMuteByDialog(): void
    {
        $chatId = $this->createChat();
        $dialogId = sprintf('chat%d', $chatId);

        $this->assertTrue($this->chatService->muteByDialog($dialogId, true)->isSuccess());
        $this->assertTrue($this->chatService->muteByDialog($dialogId, false)->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('im.chat.updateAvatar returns success for a valid base64 payload')]
    public function testUpdateAvatar(): void
    {
        $chatId = $this->createChat();
        // 1x1 transparent PNG
        $avatar = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNgAAIAAAUAAen63NgAAAAASUVORK5CYII=';

        $updatedItemResult = $this->chatService->updateAvatar($chatId, $avatar);

        $this->assertTrue($updatedItemResult->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('im.chat.updateColor returns success for every supported chat color')]
    public function testUpdateColor(): void
    {
        $chatId = $this->createChat();

        foreach ([ChatColor::Red, ChatColor::Graphite] as $chatColor) {
            $this->assertTrue($this->chatService->updateColor($chatId, $chatColor)->isSuccess());
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('im.chat.updateTitle returns success with a unique title')]
    public function testUpdateTitle(): void
    {
        $chatId = $this->createChat();
        $title = sprintf('Updated %s', uniqid('', true));

        $updatedItemResult = $this->chatService->updateTitle($chatId, $title);

        $this->assertTrue($updatedItemResult->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('im.chat.setOwner transfers chat ownership to another member')]
    public function testSetOwner(): void
    {
        $otherUserId = $this->findAnotherUserId();
        if ($otherUserId === null) {
            $this->markTestSkipped('Portal has no second user to transfer ownership to.');
        }

        $chatId = $this->createChat([$this->currentUserId, $otherUserId]);

        $updatedItemResult = $this->chatService->setOwner($chatId, $otherUserId);

        $this->assertTrue($updatedItemResult->isSuccess());
    }

    /**
     * @param list<positive-int>|null $users
     *
     * @throws BaseException
     * @throws TransportException
     */
    private function createChat(
        ?array $users = null,
        ?ChatEntityType $chatEntityType = null,
        ?string $entityId = null,
    ): int {
        $chatId = $this->chatService->add(
            users: $users ?? [$this->currentUserId],
            chatType: ChatType::Closed,
            title: sprintf('IT chat %s', uniqid('', true)),
            chatEntityType: $chatEntityType,
            entityId: $entityId,
        )->getId();

        $this->createdChats[] = $chatId;

        return $chatId;
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    private function findAnotherUserId(): ?int
    {
        $response = $this->chatService->core->call('user.get', [
            'FILTER' => ['ACTIVE' => true],
        ])->getResponseData()->getResult();

        foreach ($response as $user) {
            $id = (int)($user['ID'] ?? 0);
            if ($id > 0 && $id !== $this->currentUserId) {
                return $id;
            }
        }

        return null;
    }
}
