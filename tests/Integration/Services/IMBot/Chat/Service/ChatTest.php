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

namespace Bitrix24\SDK\Tests\Integration\Services\IMBot\Chat\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\IMBot\Bot\BotEventMode;
use Bitrix24\SDK\Services\IMBot\Bot\BotType;
use Bitrix24\SDK\Services\IMBot\Bot\Service\Bot;
use Bitrix24\SDK\Services\IMBot\Chat\Result\ChatLeaveResult;
use Bitrix24\SDK\Services\IMBot\Chat\Service\Chat;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Chat::class)]
class ChatTest extends TestCase
{
    private Chat $chatService;

    private Bot $botService;

    private int $botId = 0;

    /**
     * @var list<int>
     */
    private array $createdChatIds = [];

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function setUp(): void
    {
        $imBotServiceBuilder = Fabric::getServiceBuilder(true)->getIMBotScope();
        $this->chatService = $imBotServiceBuilder->chat();
        $this->botService = $imBotServiceBuilder->bot();

        $code = sprintf('test_chat_bot_%s', uniqid('', true));
        $botResult = $this->botService->register(
            code: $code,
            properties: ['name' => 'Chat Test Bot'],
            type: BotType::bot,
            eventMode: BotEventMode::fetch
        );
        $this->botId = $botResult->bot()->id;
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function tearDown(): void
    {
        foreach ($this->createdChatIds as $createdChatId) {
            try {
                $this->chatService->leave($this->botId, $createdChatId);
            } catch (BaseException) {
                // chat may already be left
            }
        }

        try {
            $this->botService->unregister($this->botId);
        } catch (BaseException) {
            // bot may already be deleted
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('imbot.v2.Chat.add creates a new chat and returns ChatItemResult')]
    public function testAdd(): void
    {
        $currentUserId = $this->getCurrentUserId();
        $chatResult = $this->chatService->add(
            botId: $this->botId,
            userIds: [$currentUserId],
            title: sprintf('Test Chat %s', uniqid('', true))
        );

        $chat = $chatResult->chat();
        $this->createdChatIds[] = $chat->id;

        $this->assertGreaterThan(0, $chat->id);
        $this->assertNotEmpty($chat->dialogId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('imbot.v2.Chat.leave removes the bot from the chat')]
    public function testLeave(): void
    {
        $currentUserId = $this->getCurrentUserId();
        $chatId = $this->chatService->add(
            botId: $this->botId,
            userIds: [$currentUserId],
            title: sprintf('Leave Test %s', uniqid('', true))
        )->chat()->id;

        $chatLeaveResult = $this->chatService->leave($this->botId, $chatId);

        $this->assertInstanceOf(ChatLeaveResult::class, $chatLeaveResult);
        $this->assertTrue($chatLeaveResult->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    private function getCurrentUserId(): int
    {
        return (int)$this->chatService->core
            ->call('PROFILE')->getResponseData()->getResult()['ID'];
    }
}
