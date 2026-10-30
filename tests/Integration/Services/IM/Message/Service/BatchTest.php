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

namespace Bitrix24\SDK\Tests\Integration\Services\IM\Message\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\IM\Message\Attach\RawAttach;
use Bitrix24\SDK\Services\IM\Message\Service\Batch;
use Bitrix24\SDK\Services\IM\Message\Service\Message;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Batch::class)]
class BatchTest extends TestCase
{
    private Message $messageService;

    private ?int $chatId = null;

    #[\Override]
    protected function setUp(): void
    {
        $this->messageService = Fabric::getServiceBuilder()->getIMScope()->message();
    }

    #[\Override]
    protected function tearDown(): void
    {
        if ($this->chatId !== null) {
            try {
                $this->messageService->core->call('im.chat.leave', ['CHAT_ID' => $this->chatId]);
            } catch (BaseException) {
                // chat may already be left
            }
        }

        $this->chatId = null;
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('Batch im.message.add, im.message.update and im.message.delete manage several messages')]
    public function testAddUpdateDelete(): void
    {
        $dialogId = 'chat' . $this->createChat();

        $messages = [];
        for ($i = 1; $i <= 3; $i++) {
            $messages[] = [
                'DIALOG_ID' => $dialogId,
                'MESSAGE' => sprintf('Batch message %d', $i),
            ];
        }

        $messageIds = [];
        foreach ($this->messageService->batch->add($messages) as $addedItemBatchResult) {
            $messageIds[] = $addedItemBatchResult->getId();
        }

        $this->assertCount(3, $messageIds);
        foreach ($messageIds as $messageId) {
            $this->assertGreaterThan(0, $messageId);
        }

        $updates = array_map(
            static fn (int $messageId): array => [
                'MESSAGE_ID' => $messageId,
                'MESSAGE' => sprintf('Updated batch message %d', $messageId),
            ],
            $messageIds
        );
        foreach ($this->messageService->batch->update($updates) as $updatedItemBatchResult) {
            $this->assertTrue($updatedItemBatchResult->isSuccess());
        }

        foreach ($this->messageService->batch->delete($messageIds) as $deletedItemBatchResult) {
            $this->assertTrue($deletedItemBatchResult->isSuccess());
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('Batch im.message.add builds typed attachments')]
    public function testAddWithTypedAttach(): void
    {
        $dialogId = 'chat' . $this->createChat();

        $messageIds = [];
        $messages = [
            [
                'DIALOG_ID' => $dialogId,
                'MESSAGE' => 'Batch message with attach',
                'ATTACH' => RawAttach::fromArray([['MESSAGE' => 'Attach text']]),
            ],
        ];
        foreach ($this->messageService->batch->add($messages) as $addedItemBatchResult) {
            $messageIds[] = $addedItemBatchResult->getId();
        }

        $this->assertCount(1, $messageIds);
        $this->assertGreaterThan(0, $messageIds[0]);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    private function createChat(): int
    {
        $userId = (int)$this->messageService->core->call('PROFILE')->getResponseData()->getResult()['ID'];

        $this->chatId = (int)$this->messageService->core->call('im.chat.add', [
            'USERS' => [$userId],
            'TYPE' => 'CHAT',
            'TITLE' => sprintf('IT IM Message Batch %s', uniqid('', true)),
        ])->getResponseData()->getResult()[0];

        return $this->chatId;
    }
}
