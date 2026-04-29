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
use Bitrix24\SDK\Services\IM\Chat\ChatType;
use Bitrix24\SDK\Services\IM\Chat\Service\Chat;
use Bitrix24\SDK\Services\IM\Chat\Service\ChatUser;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(ChatUser::class)]
class ChatUserTest extends TestCase
{
    private Chat $chatService;

    private ChatUser $chatUserService;

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
        $imScope = Factory::getServiceBuilder()->getIMScope();
        $this->chatService = $imScope->chat();
        $this->chatUserService = $imScope->chatUser();
        $this->currentUserId = (int)$this->chatUserService->core
            ->call('PROFILE')->getResponseData()->getResult()['ID'];
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function tearDown(): void
    {
        foreach ($this->createdChats as $chatId) {
            try {
                $this->chatService->leave($chatId);
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
    #[TestDox('im.chat.user.list returns participant user IDs for a chat')]
    public function testList(): void
    {
        $otherUserId = $this->findAnotherUserId();
        if ($otherUserId === null) {
            $this->markTestSkipped('Portal has no second user to test participant list.');
        }

        $chatId = $this->createChat([$this->currentUserId, $otherUserId]);

        $userIds = $this->chatUserService->list($chatId)->getUserIds();

        $this->assertContains($this->currentUserId, $userIds);
        $this->assertContains($otherUserId, $userIds);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('im.chat.user.add adds a new participant to a chat')]
    public function testAdd(): void
    {
        $otherUserId = $this->findAnotherUserId();
        if ($otherUserId === null) {
            $this->markTestSkipped('Portal has no second user to add to a chat.');
        }

        $chatId = $this->createChat([$this->currentUserId]);

        $result = $this->chatUserService->add($chatId, [$otherUserId]);

        $this->assertTrue($result->isSuccess());
        $this->assertContains($otherUserId, $this->chatUserService->list($chatId)->getUserIds());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('im.chat.user.delete removes a participant from a chat')]
    public function testDelete(): void
    {
        $otherUserId = $this->findAnotherUserId();
        if ($otherUserId === null) {
            $this->markTestSkipped('Portal has no second user to remove from a chat.');
        }

        $chatId = $this->createChat([$this->currentUserId, $otherUserId]);

        $result = $this->chatUserService->delete($chatId, $otherUserId);

        $this->assertTrue($result->isSuccess());
    }

    /**
     * @param list<positive-int>|null $users
     *
     * @throws BaseException
     * @throws TransportException
     */
    private function createChat(?array $users = null): int
    {
        $chatId = $this->chatService->add(
            users: $users ?? [$this->currentUserId],
            chatType: ChatType::Closed,
            title: sprintf('IT ChatUser %s', uniqid('', true)),
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
        $response = $this->chatUserService->core->call('user.get', [
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
