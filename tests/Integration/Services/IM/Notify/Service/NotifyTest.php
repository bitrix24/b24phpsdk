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

namespace Bitrix24\SDK\Tests\Integration\Services\IM\Notify\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\IM\Notify\Service\Notify;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Notify::class)]
class NotifyTest extends TestCase
{
    private Notify $imNotifyService;

    private int $currentUserId;

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('Test send notification from system')]
    public function testFromSystem(): void
    {
        $addedItemResult = $this->imNotifyService->fromSystem($this->currentUserId, sprintf('Test message at %s', time()));
        $this->assertGreaterThan(0, $addedItemResult->getId());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('Test send notification from personal')]
    public function testFromPersonal(): void
    {
        $addedItemResult = $this->imNotifyService->fromPersonal($this->currentUserId, sprintf('Test message at %s', time()));
        $this->assertGreaterThan(0, $addedItemResult->getId());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('Test send notification via im.notify')]
    public function testSend(): void
    {
        $addedItemResult = $this->imNotifyService->send(
            $this->currentUserId,
            sprintf('Test notify at %s', time()),
            'USER'
        );
        $this->assertGreaterThan(0, $addedItemResult->getId());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('Test delete notification')]
    public function testDelete(): void
    {
        $addedItemResult = $this->imNotifyService->fromSystem($this->currentUserId, sprintf('Test message for delete at %s', time()));
        $this->assertGreaterThan(0, $addedItemResult->getId());
        $this->assertTrue($this->imNotifyService->delete($addedItemResult->getId())->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('Test mark as read notification')]
    public function testMarkAsRead(): void
    {
        $addedItemResult = $this->imNotifyService->fromSystem($this->currentUserId, sprintf('Test message for mark as read at %s', time()));
        $this->assertGreaterThan(0, $addedItemResult->getId());
        $this->assertTrue($this->imNotifyService->markAsRead($addedItemResult->getId())->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('Test mark messages as read via im.notify.read.list')]
    public function testMarkMessagesAsRead(): void
    {
        $ids = [];
        for ($i = 0; $i < 3; $i++) {
            $ids[] = $this->imNotifyService->fromSystem(
                $this->currentUserId,
                sprintf('Test message for bulk read at %s', time())
            )->getId();
        }

        $this->assertTrue($this->imNotifyService->markMessagesAsRead($ids)->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('Test mark messages as unread via im.notify.read.list')]
    public function testMarkMessagesAsUnread(): void
    {
        $ids = [];
        for ($i = 0; $i < 3; $i++) {
            $ids[] = $this->imNotifyService->fromSystem(
                $this->currentUserId,
                sprintf('Test message for bulk unread at %s', time())
            )->getId();
        }

        $this->assertTrue($this->imNotifyService->markMessagesAsRead($ids)->isSuccess());
        $this->assertTrue($this->imNotifyService->markMessagesAsUnread($ids)->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('Test get list of notifications')]
    public function testGetList(): void
    {
        $this->imNotifyService->fromSystem($this->currentUserId, sprintf('Test for getList at %s', time()));

        $notifiesResult = $this->imNotifyService->getList(null, null, 10);
        $this->assertIsArray($notifiesResult->notifications());
        $this->assertGreaterThanOrEqual(0, $notifiesResult->totalCount());
        $this->assertGreaterThanOrEqual(0, $notifiesResult->totalUnreadCount());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('Test mark all notifications as read')]
    public function testMarkAllAsRead(): void
    {
        $this->imNotifyService->fromSystem($this->currentUserId, sprintf('Test for markAllAsRead at %s', time()));

        $notifyReadAllResult = $this->imNotifyService->markAllAsRead();
        $this->assertTrue($notifyReadAllResult->isSuccess());
        $this->assertGreaterThanOrEqual(0, $notifyReadAllResult->newCounter());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('Test search notification history')]
    public function testHistorySearch(): void
    {
        $notifyHistorySearchResult = $this->imNotifyService->historySearch(searchText: 'test', limit: 10);
        $this->assertIsArray($notifyHistorySearchResult->notifications());
        $this->assertGreaterThanOrEqual(0, $notifyHistorySearchResult->totalResults());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('Test get notification schema')]
    public function testGetSchema(): void
    {
        $notifySchemaResult = $this->imNotifyService->getSchema();
        $this->assertNotEmpty($notifySchemaResult->schema());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('Test Interaction with notification buttons')]
    public function testConfirm(): void
    {
        $addedItemResult = $this->imNotifyService->fromPersonal($this->currentUserId, sprintf('Test message at %s', time()));
        $this->assertGreaterThan(0, $addedItemResult->getId());
        $this->imNotifyService->confirm($addedItemResult->getId(), true);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('Test response to notification with quick reply')]
    public function testAnswer(): void
    {
        $addedItemResult = $this->imNotifyService->fromPersonal($this->currentUserId, sprintf('Test message at %s', time()));
        $this->assertGreaterThan(0, $addedItemResult->getId());
        $this->imNotifyService->answer($addedItemResult->getId(), 'reply text');
    }

    #[\Override]
    protected function setUp(): void
    {
        $this->imNotifyService = Factory::getServiceBuilder()->getIMScope()->notify();
        $this->currentUserId = (int)$this->imNotifyService->core->call('PROFILE')
            ->getResponseData()->getResult()['ID'];
    }
}
