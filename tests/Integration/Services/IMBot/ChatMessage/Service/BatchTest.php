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

namespace Bitrix24\SDK\Tests\Integration\Services\IMBot\ChatMessage\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\IMBot\Bot\BotEventMode;
use Bitrix24\SDK\Services\IMBot\Bot\BotType;
use Bitrix24\SDK\Services\IMBot\Bot\Service\Bot;
use Bitrix24\SDK\Services\IMBot\Chat\Service\Chat;
use Bitrix24\SDK\Services\IMBot\ChatMessage\Service\Batch;
use Bitrix24\SDK\Services\IMBot\ChatMessage\Service\ChatMessage;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Batch::class)]
class BatchTest extends TestCase
{
    private Bot $botService;

    private Chat $chatService;

    private ChatMessage $chatMessageService;

    private int $botId;

    private string $dialogId;

    private int $chatId;

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function setUp(): void
    {
        $scope = Factory::getServiceBuilder(true)->getIMBotScope();
        $this->botService = $scope->bot();
        $this->chatService = $scope->chat();
        $this->chatMessageService = $scope->chatMessage();

        // Register a test bot
        $code = sprintf('test_batch_msg_bot_%s', uniqid('', true));
        $botResult = $this->botService->register(
            code: $code,
            properties: ['name' => 'Test Batch Message Bot'],
            type: BotType::bot,
            eventMode: BotEventMode::fetch
        );
        $this->botId = $botResult->bot()->id;

        // Create a group chat on behalf of the bot
        $chatResult = $this->chatService->add(
            botId: $this->botId,
            userIds: [],
            title: sprintf('Batch Test Chat %s', uniqid('', true))
        );
        $chat = $chatResult->chat();
        $this->chatId = $chat->id;
        $this->dialogId = $chat->dialogId;
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function tearDown(): void
    {
        try {
            $this->chatService->leave($this->botId, $this->chatId);
        } catch (BaseException) {
            // chat may already be gone
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
    #[TestDox('imbot.v2.Chat.Message.send batch sends multiple messages and returns IDs')]
    public function testBatchSend(): void
    {
        $messages = [
            [
                'botId'    => $this->botId,
                'dialogId' => $this->dialogId,
                'fields'   => ['message' => 'Batch message 1'],
            ],
            [
                'botId'    => $this->botId,
                'dialogId' => $this->dialogId,
                'fields'   => ['message' => 'Batch message 2'],
            ],
            [
                'botId'    => $this->botId,
                'dialogId' => $this->dialogId,
                'fields'   => ['message' => 'Batch message 3'],
            ],
        ];

        $ids = [];
        foreach ($this->chatMessageService->batch->send($messages) as $result) {
            $ids[] = $result->getId();
        }

        $this->assertCount(count($messages), $ids);
        foreach ($ids as $id) {
            $this->assertGreaterThan(0, $id);
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('imbot.v2.Chat.Message.delete batch deletes multiple messages')]
    public function testBatchDelete(): void
    {
        // First send some messages to delete
        $sendParams = [
            [
                'botId'    => $this->botId,
                'dialogId' => $this->dialogId,
                'fields'   => ['message' => 'To delete 1'],
            ],
            [
                'botId'    => $this->botId,
                'dialogId' => $this->dialogId,
                'fields'   => ['message' => 'To delete 2'],
            ],
        ];

        $messageIds = [];
        foreach ($this->chatMessageService->batch->send($sendParams) as $result) {
            $messageIds[] = $result->getId();
        }

        $this->assertCount(count($sendParams), $messageIds);

        // Now delete in batch
        $deleteParams = array_map(
            fn(int $id): array => ['botId' => $this->botId, 'messageId' => $id],
            $messageIds
        );

        $deletedCount = 0;
        foreach ($this->chatMessageService->batch->delete($deleteParams) as $result) {
            $deletedCount++;
        }

        $this->assertSame(count($messageIds), $deletedCount);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('imbot.v2.Chat.Message.update batch updates multiple messages')]
    public function testBatchUpdate(): void
    {
        // First send some messages to update
        $sendParams = [
            [
                'botId'    => $this->botId,
                'dialogId' => $this->dialogId,
                'fields'   => ['message' => 'Original 1'],
            ],
            [
                'botId'    => $this->botId,
                'dialogId' => $this->dialogId,
                'fields'   => ['message' => 'Original 2'],
            ],
        ];

        $messageIds = [];
        foreach ($this->chatMessageService->batch->send($sendParams) as $result) {
            $messageIds[] = $result->getId();
        }

        $this->assertCount(count($sendParams), $messageIds);

        // Update them in batch
        $updateParams = array_map(
            fn(int $id, int $idx): array => [
                'botId'     => $this->botId,
                'messageId' => $id,
                'fields'    => ['message' => sprintf('Updated %d', $idx + 1)],
            ],
            $messageIds,
            array_keys($messageIds)
        );

        $updatedCount = 0;
        foreach ($this->chatMessageService->batch->update($updateParams) as $result) {
            $updatedCount++;
        }

        $this->assertSame(count($messageIds), $updatedCount);
    }
}
