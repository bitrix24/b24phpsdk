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
use Bitrix24\SDK\Tests\Integration\Factory;
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
        $result = $this->imNotifyService->fromSystem($this->currentUserId, sprintf('Test message at %s', time()));
        $this->assertGreaterThan(0, $result->getId());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('Test send notification from personal')]
    public function testFromPersonal(): void
    {
        $result = $this->imNotifyService->fromPersonal($this->currentUserId, sprintf('Test message at %s', time()));
        $this->assertGreaterThan(0, $result->getId());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('Test send notification via im.notify')]
    public function testSend(): void
    {
        $result = $this->imNotifyService->send(
            $this->currentUserId,
            sprintf('Test notify at %s', time()),
            'USER'
        );
        $this->assertGreaterThan(0, $result->getId());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('Test delete notification')]
    public function testDelete(): void
    {
        $added = $this->imNotifyService->fromSystem($this->currentUserId, sprintf('Test message for delete at %s', time()));
        $this->assertGreaterThan(0, $added->getId());
        $this->assertTrue($this->imNotifyService->delete($added->getId())->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('Test mark as read notification')]
    public function testMarkAsRead(): void
    {
        $added = $this->imNotifyService->fromSystem($this->currentUserId, sprintf('Test message for mark as read at %s', time()));
        $this->assertGreaterThan(0, $added->getId());
        $this->assertTrue($this->imNotifyService->markAsRead($added->getId())->isSuccess());
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

        $result = $this->imNotifyService->getList(null, null, 10);
        $this->assertIsArray($result->notifications());
        $this->assertGreaterThanOrEqual(0, $result->totalCount());
        $this->assertGreaterThanOrEqual(0, $result->totalUnreadCount());
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

        $result = $this->imNotifyService->markAllAsRead();
        $this->assertTrue($result->isSuccess());
        $this->assertGreaterThanOrEqual(0, $result->newCounter());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('Test search notification history')]
    public function testHistorySearch(): void
    {
        $result = $this->imNotifyService->historySearch(searchText: 'test', limit: 10);
        $this->assertIsArray($result->notifications());
        $this->assertGreaterThanOrEqual(0, $result->totalResults());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('Test get notification schema')]
    public function testGetSchema(): void
    {
        $result = $this->imNotifyService->getSchema();
        $this->assertNotEmpty($result->schema());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('Test Interaction with notification buttons')]
    public function testConfirm(): void
    {
        $added = $this->imNotifyService->fromPersonal($this->currentUserId, sprintf('Test message at %s', time()));
        $this->assertGreaterThan(0, $added->getId());
        $this->imNotifyService->confirm($added->getId(), true);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('Test response to notification with quick reply')]
    public function testAnswer(): void
    {
        $added = $this->imNotifyService->fromPersonal($this->currentUserId, sprintf('Test message at %s', time()));
        $this->assertGreaterThan(0, $added->getId());
        $this->imNotifyService->answer($added->getId(), 'reply text');
    }

    #[\Override]
    protected function setUp(): void
    {
        $this->imNotifyService = Factory::getServiceBuilder()->getIMScope()->notify();
        $this->currentUserId = (int)$this->imNotifyService->core->call('PROFILE')
            ->getResponseData()->getResult()['ID'];
    }
}
