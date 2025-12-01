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
use Bitrix24\SDK\Services\Calendar\Event\Result\EventItemResult;
use Bitrix24\SDK\Services\Calendar\Event\Service\Event;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use Bitrix24\SDK\Services\ServiceBuilder;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

/**
 * Class EventTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Calendar\Event\Service
 */
#[CoversMethod(Event::class, 'add')]
#[CoversMethod(Event::class, 'update')]
#[CoversMethod(Event::class, 'getById')]
#[CoversMethod(Event::class, 'get')]
#[CoversMethod(Event::class, 'getNearest')]
#[CoversMethod(Event::class, 'delete')]
#[CoversMethod(Event::class, 'getMeetingStatus')]
#[CoversMethod(Event::class, 'getAccessibility')]
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\Calendar\Event\Service\Event::class)]
class EventTest extends TestCase
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
        $calendarName = 'Test Calendar ' . uniqid();
        $calendarFields = [
            'description' => 'Test calendar for integration tests',
            'color' => '#9cbe1c',
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
    public function testAdd(): void
    {
        $eventFields = [
            'type' => 'user',
            'ownerId' => $this->userId,
            'section' => $this->calendarId,
            'name' => 'Test Event ' . uniqid(),
            'description' => 'Test event description',
            'from_ts' => strtotime('+1 hour'),
            'to_ts' => strtotime('+5 hours'),
            'skip_time' => 'N',
            'accessibility' => 'busy',
            'importance' => 'normal',
            'is_meeting' => 'N',
            'private_event' => 'N'
        ];

        $addedItemResult = $this->eventService->add($eventFields);

        $this->assertIsNumeric($addedItemResult->getId());
        $this->assertGreaterThan(0, $addedItemResult->getId());

        // Clean up
        $this->eventService->delete($addedItemResult->getId());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetById(): void
    {
        // Create test event
        $eventFields = [
            'type' => 'user',
            'ownerId' => $this->userId,
            'section' => $this->calendarId,
            'name' => 'Test Event for GetById',
            'from_ts' => strtotime('+1 hour'),
            'to_ts' => strtotime('+5 hours'),
        ];

        $addedItemResult = $this->eventService->add($eventFields);
        $eventId = $addedItemResult->getId();

        // Get event by ID
        $eventResult = $this->eventService->getById($eventId);
        $event = $eventResult->event();

        $this->assertEquals($eventId, (int)$event->ID);
        $this->assertEquals($eventFields['name'], $event->NAME);
        $this->assertEquals($this->userId, (int)$event->OWNER_ID);

        // Clean up
        $this->eventService->delete($eventId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testUpdate(): void
    {
        // Create test event
        $eventFields = [
            'type' => 'user',
            'ownerId' => $this->userId,
            'section' => $this->calendarId,
            'name' => 'Original Event Name',
            'from_ts' => strtotime('+1 hour'),
            'to_ts' => strtotime('+5 hours'),
        ];

        $addedItemResult = $this->eventService->add($eventFields);
        $eventId = $addedItemResult->getId();

        // Update event
        $updateFields = [
            'id' => $eventId,
            'type' => 'user',
            'ownerId' => $this->userId,
            'name' => 'Updated Event Name',
            'description' => 'Updated description'
        ];

        $updatedItemResult = $this->eventService->update($updateFields);
        $this->assertTrue($updatedItemResult->isSuccess());

        // Verify update
        $eventResult = $this->eventService->getById($eventId);
        $eventItemResult = $eventResult->event();

        $this->assertEquals('Updated Event Name', $eventItemResult->NAME);
        $this->assertEquals('Updated description', $eventItemResult->DESCRIPTION);

        // Clean up
        $this->eventService->delete($eventId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGet(): void
    {
        // Create test event
        $eventFields = [
            'type' => 'user',
            'ownerId' => $this->userId,
            'section' => $this->calendarId,
            'name' => 'Test Event for Get',
            'from_ts' => strtotime('+1 hour'),
            'to_ts' => strtotime('+5 hours'),
        ];

        $addedItemResult = $this->eventService->add($eventFields);
        $eventId = $addedItemResult->getId();

        // Get events list
        $eventsResult = $this->eventService->get('user', $this->userId, [
            'from' => date('Y-m-d', strtotime('today')),
            'to' => date('Y-m-d', strtotime('+1 month'))
        ]);

        $events = $eventsResult->getEvents();
        $this->assertIsArray($events);
        $this->assertGreaterThanOrEqual(1, count($events));

        $foundEvent = null;
        foreach ($events as $event) {
            if ((int)$event->ID === $eventId) {
                $foundEvent = $event;
                break;
            }
        }

        $this->assertNotNull($foundEvent, 'Created event should be found in events list');
        $this->assertEquals($eventFields['name'], $foundEvent->NAME);

        // Clean up
        $this->eventService->delete($eventId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetNearest(): void
    {
        // Create test event in the near future
        $eventFields = [
            'type' => 'user',
            'ownerId' => $this->userId,
            'section' => $this->calendarId,
            'name' => 'Test Nearest Event',
            'from_ts' => strtotime('+1 hour'),
            'to_ts' => strtotime('+5 hours'),
        ];

        $addedItemResult = $this->eventService->add($eventFields);
        $eventId = $addedItemResult->getId();

        // Get nearest events
        $eventsResult = $this->eventService->getNearest([
            'type' => 'user',
            'ownerId' => $this->userId,
            'days' => 7,
            'maxEventsCount' => 50
        ]);

        $events = $eventsResult->getEvents();
        $this->assertIsArray($events);

        // Check if our event is in the list
        $foundEvent = null;
        foreach ($events as $event) {
            if ((int)$event->ID === $eventId) {
                $foundEvent = $event;
                break;
            }
        }

        $this->assertNotNull($foundEvent, 'Created event should be found in nearest events');

        // Clean up
        $this->eventService->delete($eventId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelete(): void
    {
        // Create test event
        $eventFields = [
            'type' => 'user',
            'ownerId' => $this->userId,
            'section' => $this->calendarId,
            'name' => 'Event to Delete',
            'from_ts' => strtotime('+1 hour'),
            'to_ts' => strtotime('+5 hours'),
        ];

        $addedItemResult = $this->eventService->add($eventFields);
        $eventId = $addedItemResult->getId();

        // Delete event
        $deletedItemResult = $this->eventService->delete($eventId);
        $this->assertTrue($deletedItemResult->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testMeetingStatus(): void
    {
        // Create test meeting event
        $eventFields = [
            'type' => 'user',
            'ownerId' => $this->userId,
            'section' => $this->calendarId,
            'name' => 'Test Meeting Event',
            'from_ts' => strtotime('+1 hour'),
            'to_ts' => strtotime('+5 hours'),
            'is_meeting' => 'Y',
            'attendees' => [$this->userId],
            'host' => $this->userId,
            'private_event' => 'N',
        ];

        $addedItemResult = $this->eventService->add($eventFields);
        $eventId = $addedItemResult->getId();

        // Get initial meeting status
        $meetingStatusResult = $this->eventService->getMeetingStatus($eventId);
        $initialStatus = $meetingStatusResult->getMeetingStatus();

        $this->assertContains($initialStatus, ['Y', 'N', 'Q', 'H']);

        // Clean up
        $this->eventService->delete($eventId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetAccessibility(): void
    {
        // Create test event to make user busy
        $eventFields = [
            'type' => 'user',
            'ownerId' => $this->userId,
            'section' => $this->calendarId,
            'name' => 'Busy Event',
            'from_ts' => strtotime('+1 hour'),
            'to_ts' => strtotime('+5 hours'),
            'accessibility' => 'busy'
        ];

        $addedItemResult = $this->eventService->add($eventFields);
        $eventId = $addedItemResult->getId();

        // Check user accessibility
        $accessibilityResult = $this->eventService->getAccessibility(
            [$this->userId],
            date('Y-m-d', strtotime('today')),
            date('Y-m-d', strtotime('+1 week'))
        );

        $accessibility = $accessibilityResult->getUsersAccessibility();
        $this->assertIsArray($accessibility);
        $this->assertArrayHasKey((string)$this->userId, $accessibility);

        $userEvents = $accessibility[$this->userId];

        // Should find our busy event
        $foundBusyEvent = false;
        foreach ($userEvents as $userEvent) {
            if ((int)$userEvent->ID === $eventId) {
                $foundBusyEvent = true;
                $this->assertEquals('busy', $userEvent->ACCESSIBILITY);
                break;
            }
        }

        $this->assertTrue($foundBusyEvent, 'Created busy event should be found in accessibility data');

        // Clean up
        $this->eventService->delete($eventId);
    }
}