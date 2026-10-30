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

namespace Bitrix24\SDK\Tests\Integration\Services\IM\Notify\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\IM\Notify\Service\Batch;
use Bitrix24\SDK\Services\IM\Notify\Service\Notify;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Batch::class)]
class BatchTest extends TestCase
{
    private Notify $notifyService;

    private int $currentUserId = 0;

    /**
     * @var int[]
     */
    private array $createdNotifications = [];

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->notifyService = Fabric::getServiceBuilder()->getIMScope()->notify();
        $this->currentUserId = (int)$this->notifyService->core
            ->call('PROFILE')->getResponseData()->getResult()['ID'];
    }

    #[\Override]
    protected function tearDown(): void
    {
        foreach ($this->createdNotifications as $createdNotification) {
            try {
                $this->notifyService->delete($createdNotification);
            } catch (BaseException) {
                // notification may already be deleted
            }
        }

        $this->createdNotifications = [];
    }

    /**
     * @throws BaseException
     */
    #[Test]
    #[TestDox('Batch im.notify.system.add sends several system notifications')]
    public function testFromSystem(): void
    {
        $notificationIds = [];
        foreach ($this->notifyService->batch->fromSystem($this->buildNotifications('System')) as $addedItemBatchResult) {
            $notificationIds[] = $addedItemBatchResult->getId();
        }

        $this->createdNotifications = $notificationIds;

        $this->assertCount(3, $notificationIds);
        foreach ($notificationIds as $notificationId) {
            $this->assertGreaterThan(0, $notificationId);
        }
    }

    /**
     * @throws BaseException
     */
    #[Test]
    #[TestDox('Batch im.notify.personal.add sends several personal notifications')]
    public function testFromPersonal(): void
    {
        $notificationIds = [];
        foreach ($this->notifyService->batch->fromPersonal($this->buildNotifications('Personal')) as $addedItemBatchResult) {
            $notificationIds[] = $addedItemBatchResult->getId();
        }

        $this->createdNotifications = $notificationIds;

        $this->assertCount(3, $notificationIds);
        foreach ($notificationIds as $notificationId) {
            $this->assertGreaterThan(0, $notificationId);
        }
    }

    /**
     * @throws BaseException
     */
    #[Test]
    #[TestDox('Batch im.notify.read.list marks notifications as read and unread')]
    public function testMarkMessagesAsReadAndUnread(): void
    {
        $notificationIds = $this->createNotifications();

        $readResults = iterator_to_array($this->notifyService->batch->markMessagesAsRead($notificationIds), false);
        $this->assertCount(1, $readResults);
        $this->assertTrue($readResults[0]->isSuccess());

        $unreadResults = iterator_to_array($this->notifyService->batch->markMessagesAsUnread($notificationIds), false);
        $this->assertCount(1, $unreadResults);
        $this->assertTrue($unreadResults[0]->isSuccess());
    }

    /**
     * @throws BaseException
     */
    #[Test]
    #[TestDox('Batch im.notify.delete deletes several notifications')]
    public function testDelete(): void
    {
        $notificationIds = $this->createNotifications();

        $results = iterator_to_array($this->notifyService->batch->delete($notificationIds), false);

        $this->assertCount(count($notificationIds), $results);
        foreach ($results as $result) {
            $this->assertTrue($result->isSuccess());
        }

        $this->createdNotifications = [];
    }

    /**
     * @return int[]
     *
     * @throws BaseException
     */
    private function createNotifications(): array
    {
        $notificationIds = [];
        foreach ($this->notifyService->batch->fromSystem($this->buildNotifications('Batch')) as $addedItemBatchResult) {
            $notificationIds[] = $addedItemBatchResult->getId();
        }

        $this->createdNotifications = $notificationIds;

        return $notificationIds;
    }

    /**
     * @return array<int, array{USER_ID: int, MESSAGE: string}>
     */
    private function buildNotifications(string $prefix): array
    {
        $notifications = [];
        for ($i = 1; $i <= 3; $i++) {
            $notifications[] = [
                'USER_ID' => $this->currentUserId,
                'MESSAGE' => sprintf('%s batch notification %d at %s', $prefix, $i, time()),
            ];
        }

        return $notifications;
    }
}
