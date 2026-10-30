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
use Bitrix24\SDK\Services\IM\Chat\Service\Batch;
use Bitrix24\SDK\Services\IM\Chat\Service\Chat;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Batch::class)]
class BatchTest extends TestCase
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
     */
    #[Test]
    #[TestDox('Batch im.chat.add creates several chats')]
    public function testAdd(): void
    {
        $chats = [];
        for ($i = 1; $i <= 3; $i++) {
            $chats[] = [
                'USERS' => [$this->currentUserId],
                'TYPE' => ChatType::Closed->value,
                'TITLE' => sprintf('IT IM Chat Batch %d %s', $i, uniqid('', true)),
            ];
        }

        $chatIds = [];
        foreach ($this->chatService->batch->add($chats) as $addedItemBatchResult) {
            $chatIds[] = $addedItemBatchResult->getId();
        }

        $this->createdChats = $chatIds;

        $this->assertCount(3, $chatIds);
        foreach ($chatIds as $chatId) {
            $this->assertGreaterThan(0, $chatId);
        }

        $this->assertCount(3, array_unique($chatIds));
    }
}
