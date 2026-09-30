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

namespace Bitrix24\SDK\Tests\Integration\Services\IM\Recent\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\IM\Recent\Result\RecentItemResult;
use Bitrix24\SDK\Services\IM\Recent\Service\Batch;
use Bitrix24\SDK\Services\IM\Recent\Service\Recent;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Batch::class)]
class BatchTest extends TestCase
{
    private Recent $recentService;

    private ?int $chatId = null;

    #[\Override]
    protected function setUp(): void
    {
        $this->recentService = Factory::getServiceBuilder(true)->getIMScope()->recent();
    }

    #[\Override]
    protected function tearDown(): void
    {
        if ($this->chatId !== null) {
            try {
                $this->recentService->core->call('im.chat.leave', ['CHAT_ID' => $this->chatId]);
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
    #[TestDox('Batch im.recent.list returns recent dialogs including a new chat')]
    public function testList(): void
    {
        $this->chatId = $this->createChat();

        $items = iterator_to_array($this->recentService->batch->list(['SKIP_OPENLINES' => 'Y']), false);

        $this->assertNotEmpty($items);
        $this->assertContainsOnlyInstancesOf(RecentItemResult::class, $items);
        $this->assertContains(
            'chat' . $this->chatId,
            array_map(static fn (RecentItemResult $recentItemResult): string => (string)$recentItemResult->id, $items)
        );
    }

    /**
     * @throws BaseException
     */
    #[Test]
    #[TestDox('Batch im.recent.list respects the limit')]
    public function testListWithLimit(): void
    {
        $items = iterator_to_array($this->recentService->batch->list(limit: 1), false);

        $this->assertLessThanOrEqual(1, count($items));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    private function createChat(): int
    {
        $userId = (int)$this->recentService->core->call('PROFILE')->getResponseData()->getResult()['ID'];

        return (int)$this->recentService->core->call('im.chat.add', [
            'USERS' => [$userId],
            'TYPE' => 'CHAT',
            'TITLE' => sprintf('IT IM Recent Batch %s', uniqid('', true)),
            'MESSAGE' => 'Recent batch test message',
        ])->getResponseData()->getResult()[0];
    }
}
