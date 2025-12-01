<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Sally Fancen <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\Calendar\Event\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Calendar\Event\Service\Event;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use Bitrix24\SDK\Services\ServiceBuilder;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

/**
 * Class BatchTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Calendar\Event\Service
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\Calendar\Event\Service\Event::class)]
class BatchTest extends TestCase
{
    use CustomBitrix24Assertions;

    protected Event $eventService;

    protected ServiceBuilder $serviceBuilder;

    protected int $calendarId;

    protected int $userId;

    /**
     * @throws BaseException
     * @throws TransportException
     */
    protected function setUp(): void
    {
        $this->serviceBuilder = Factory::getServiceBuilder();
        $this->eventService = $this->serviceBuilder->getCalendarScope()->event();

        // Get current user ID
        $userProfileItemResult = $this->serviceBuilder->getMainScope()->main()->getCurrentUserProfile()->getUserProfile();
        $this->userId = (int)$userProfileItemResult->ID;

        // Create test calendar section
        $calendarName = 'Batch Test Calendar ' . uniqid();
        $calendarFields = [
            'description' => 'Test calendar for batch operations',
            'color' => '#ff6600',
            'active' => 'Y'
        ];

        $this->calendarId = $this->serviceBuilder->getCalendarScope()->calendar()->add('user', $this->userId, $calendarName, $calendarFields)->getId();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    protected function tearDown(): void
    {
        // Delete test calendar
        if (isset($this->calendarId)) {
            $this->serviceBuilder->getCalendarScope()->calendar()->delete('user', $this->userId, $this->calendarId);
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testBatchAdd(): void
    {
        $events = [
            [
                'type' => 'user',
                'ownerId' => $this->userId,
                'section' => $this->calendarId,
                'name' => 'Batch Event 1',
                'from_ts' => strtotime('+1 hour'),
                'to_ts' => strtotime('+2 hours'),
                'description' => 'First batch event'
            ],
            [
                'type' => 'user',
                'ownerId' => $this->userId,
                'section' => $this->calendarId,
                'name' => 'Batch Event 2',
                'from_ts' => strtotime('+3 hours'),
                'to_ts' => strtotime('+4 hours'),
                'description' => 'Second batch event'
            ],
            [
                'type' => 'user',
                'ownerId' => $this->userId,
                'section' => $this->calendarId,
                'name' => 'Batch Event 3',
                'from_ts' => strtotime('+5 hours'),
                'to_ts' => strtotime('+6 hours'),
                'description' => 'Third batch event'
            ]
        ];

        $eventIds = [];
        foreach ($this->eventService->batch->add($events) as $event) {
            $this->assertIsNumeric($event->getId());
            $this->assertGreaterThan(0, $event->getId());
            $eventIds[] = $event->getId();
        }

        $this->assertCount(3, $eventIds);

        // Clean up created events
        foreach ($eventIds as $eventId) {
            $this->serviceBuilder->getCalendarScope()->event()->delete($eventId);
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testBatchUpdate(): void
    {
        // First, create some events to update
        $initialEvents = [
            [
                'type' => 'user',
                'ownerId' => $this->userId,
                'section' => $this->calendarId,
                'name' => 'Event to Update 1',
                'from_ts' => strtotime('+1 hour'),
                'to_ts' => strtotime('+2 hours'),
            ],
            [
                'type' => 'user',
                'ownerId' => $this->userId,
                'section' => $this->calendarId,
                'name' => 'Event to Update 2',
                'from_ts' => strtotime('+3 hours'),
                'to_ts' => strtotime('+5 hours'),
            ]
        ];

        $generator = $this->eventService->batch->add($initialEvents);
        $createdEvents = [];
        foreach ($generator as $event) {
            $createdEvents[] = $event;
        }

        $this->assertCount(2, $createdEvents);

        // Prepare update data
        $updateEvents = [];
        foreach ($createdEvents as $event) {
            $updateEvents[] = [
                'id' => $event->getId(),
                'type' => 'user',
                'ownerId' => $this->userId,
                'name' => 'Updated ' . $event->getId(),
                'description' => 'Updated via batch operation'
            ];
        }

        // Perform batch update
        $updateResult = $this->eventService->batch->update($updateEvents);
        $updatedEvents = [];
        foreach ($updateResult as $event) {
            $updatedEvents[] = $event;
        }

        $this->assertCount(2, $updatedEvents);

        foreach ($updatedEvents as $event) {
            $this->assertTrue($event->isSuccess());
        }

        // Clean up
        $eventIds = array_map(fn($event): int => $event->getId(), $createdEvents);
        foreach ($eventIds as $eventId) {
            $this->serviceBuilder->getCalendarScope()->event()->delete($eventId);
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testBatchDelete(): void
    {
        // First, create some events to delete
        $events = [
            [
                'type' => 'user',
                'ownerId' => $this->userId,
                'section' => $this->calendarId,
                'name' => 'Event to Delete 1',
                'from_ts' => strtotime('+1 hour'),
                'to_ts' => strtotime('+2 hours'),
            ],
            [
                'type' => 'user',
                'ownerId' => $this->userId,
                'section' => $this->calendarId,
                'name' => 'Event to Delete 2',
                'from_ts' => strtotime('+3 hours'),
                'to_ts' => strtotime('+4 hours'),
            ],
            [
                'type' => 'user',
                'ownerId' => $this->userId,
                'section' => $this->calendarId,
                'name' => 'Event to Delete 3',
                'from_ts' => strtotime('+5 hours'),
                'to_ts' => strtotime('+6 hours'),
            ]
        ];

        $generator = $this->eventService->batch->add($events);
        $createdEvents = [];
        foreach ($generator as $event) {
            $createdEvents[] = $event;
        }

        $this->assertCount(3, $createdEvents);

        // Extract event IDs for deletion
        $eventIds = array_map(fn($event): int => $event->getId(), $createdEvents);

        // Perform batch delete
        $deleteResult = $this->eventService->batch->delete($eventIds);
        $deletedCount = 0;
        foreach ($deleteResult as $result) {
            $this->assertTrue($result->isSuccess());
            $deletedCount++;
        }

        $this->assertEquals(3, $deletedCount);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testBatchOperationsWithMixedResults(): void
    {
        // Test batch add with some valid and some potentially problematic data
        $events = [
            [
                'type' => 'user',
                'ownerId' => $this->userId,
                'section' => $this->calendarId,
                'name' => 'Valid Event 1',
                'from_ts' => strtotime('+1 hour'),
                'to_ts' => strtotime('+2 hours'),
            ],
            [
                'type' => 'user',
                'ownerId' => $this->userId,
                'section' => $this->calendarId,
                'name' => 'Valid Event 2',
                'from_ts' => strtotime('+3 hours'),
                'to_ts' => strtotime('+5 hours'),
            ]
        ];

        $generator = $this->eventService->batch->add($events);
        $addedEvents = [];
        foreach ($generator as $event) {
            $addedEvents[] = $event;
        }

        $this->assertCount(2, $addedEvents);

        // Clean up
        $eventIds = array_map(fn($event): int => $event->getId(), $addedEvents);
        foreach ($eventIds as $eventId) {
            $this->serviceBuilder->getCalendarScope()->event()->delete($eventId);
        }
    }
}