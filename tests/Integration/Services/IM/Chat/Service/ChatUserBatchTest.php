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

namespace Bitrix24\SDK\Tests\Integration\Services\IM\Chat\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\IM\Chat\ChatType;
use Bitrix24\SDK\Services\IM\Chat\Service\Chat;
use Bitrix24\SDK\Services\IM\Chat\Service\ChatUser;
use Bitrix24\SDK\Services\IM\Chat\Service\ChatUserBatch;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(ChatUserBatch::class)]
class ChatUserBatchTest extends TestCase
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
        $imServiceBuilder = Fabric::getServiceBuilder()->getIMScope();
        $this->chatService = $imServiceBuilder->chat();
        $this->chatUserService = $imServiceBuilder->chatUser();
        $this->currentUserId = (int)$this->chatUserService->core
            ->call('PROFILE')->getResponseData()->getResult()['ID'];
    }

    #[\Override]
    protected function tearDown(): void
    {
        foreach ($this->createdChats as $createdChat) {
            try {
                $this->chatService->leave($createdChat);
            } catch (BaseException) {
                // chat may already be left
            }
        }

        $this->createdChats = [];
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('Batch im.chat.user.list returns participant user IDs keyed by chat ID')]
    public function testList(): void
    {
        $chatIds = [$this->createChat(), $this->createChat()];

        $participants = iterator_to_array($this->chatUserService->batch->list($chatIds));

        $this->assertSame($chatIds, array_keys($participants));
        foreach ($participants as $participant) {
            $this->assertContains($this->currentUserId, $participant);
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('Batch im.chat.user.add and im.chat.user.delete manage participants of several chats')]
    public function testAddAndDelete(): void
    {
        $otherUserId = $this->findAnotherUserId();
        if ($otherUserId === null) {
            $this->markTestSkipped('Portal has no second user to add to chats.');
        }

        $chatIds = [$this->createChat(), $this->createChat()];

        $chatUsers = array_map(
            static fn (int $chatId): array => ['CHAT_ID' => $chatId, 'USERS' => [$otherUserId], 'HIDE_HISTORY' => 'N'],
            $chatIds
        );
        foreach ($this->chatUserService->batch->add($chatUsers) as $updatedItemBatchResult) {
            $this->assertTrue($updatedItemBatchResult->isSuccess());
        }

        foreach ($this->chatUserService->batch->list($chatIds) as $userIds) {
            $this->assertContains($otherUserId, $userIds);
        }

        $chatUsers = array_map(
            static fn (int $chatId): array => ['CHAT_ID' => $chatId, 'USER_ID' => $otherUserId],
            $chatIds
        );
        foreach ($this->chatUserService->batch->delete($chatUsers) as $deletedItemBatchResult) {
            $this->assertTrue($deletedItemBatchResult->isSuccess());
        }

        foreach ($this->chatUserService->batch->list($chatIds) as $userIds) {
            $this->assertNotContains($otherUserId, $userIds);
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    private function createChat(): int
    {
        $chatId = $this->chatService->add(
            users: [$this->currentUserId],
            chatType: ChatType::Closed,
            title: sprintf('IT IM ChatUser Batch %s', uniqid('', true)),
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
