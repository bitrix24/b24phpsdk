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

namespace Bitrix24\SDK\Services\Calendar\Event\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\AddedItemResult;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Calendar\Event\Result\AccessibilityResult;
use Bitrix24\SDK\Services\Calendar\Event\Result\EventResult;
use Bitrix24\SDK\Services\Calendar\Event\Result\EventsResult;
use Bitrix24\SDK\Services\Calendar\Event\Result\MeetingStatusResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['calendar']))]
class Event extends AbstractService
{
    /**
     * Event constructor.
     */
    public function __construct(
        public Batch $batch,
        CoreInterface $core,
        LoggerInterface $logger
    ) {
        parent::__construct($core, $logger);
    }

    /**
     * Add calendar event
     *
     * @param array $fields Event fields (type, ownerId, from, to, section, name are required)
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'calendar.event.add',
        'https://apidocs.bitrix24.com/api-reference/calendar/calendar-event/calendar-event-add.html',
        'Add calendar event'
    )]
    public function add(array $fields): AddedItemResult
    {
        return new AddedItemResult(
            $this->core->call(
                'calendar.event.add',
                $fields
            )
        );
    }

    /**
     * Update calendar event
     *
     * @param array $fields Event fields (id, type, ownerId, name are required)
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'calendar.event.update',
        'https://apidocs.bitrix24.com/api-reference/calendar/calendar-event/calendar-event-update.html',
        'Update calendar event'
    )]
    public function update(array $fields): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call(
                'calendar.event.update',
                $fields
            )
        );
    }

    /**
     * Get calendar event by ID
     *
     * @param int $id Event identifier
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'calendar.event.getById',
        'https://apidocs.bitrix24.com/api-reference/calendar/calendar-event/calendar-event-get-by-id.html',
        'Get calendar event by ID'
    )]
    public function getById(int $id): EventResult
    {
        return new EventResult(
            $this->core->call(
                'calendar.event.getbyid',
                [
                    'id' => $id,
                ]
            )
        );
    }

    /**
     * Get list of calendar events
     *
     * @param string $type Calendar type
     * @param int $ownerId Calendar owner identifier
     * @param array $fields Additional filter fields
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'calendar.event.get',
        'https://apidocs.bitrix24.com/api-reference/calendar/calendar-event/calendar-event-get.html',
        'Get list of calendar events'
    )]
    public function get(string $type, int $ownerId, array $fields = []): EventsResult
    {
        $requestFields = array_merge([
            'type' => $type,
            'ownerId' => $ownerId,
        ], $fields);

        return new EventsResult(
            $this->core->call(
                'calendar.event.get',
                $requestFields
            )
        );
    }

    /**
     * Get list of upcoming calendar events
     *
     * @param array $fields Filter fields
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'calendar.event.getNearest',
        'https://apidocs.bitrix24.com/api-reference/calendar/calendar-event/calendar-event-get-nearest.html',
        'Get list of upcoming events'
    )]
    public function getNearest(array $fields = []): EventsResult
    {
        return new EventsResult(
            $this->core->call(
                'calendar.event.get.nearest',
                $fields
            )
        );
    }

    /**
     * Delete calendar event
     *
     * @param int $id Event identifier
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'calendar.event.delete',
        'https://apidocs.bitrix24.com/api-reference/calendar/calendar-event/calendar-event-delete.html',
        'Delete calendar event'
    )]
    public function delete(int $id): DeletedItemResult
    {
        return new DeletedItemResult(
            $this->core->call(
                'calendar.event.delete',
                [
                    'id' => $id,
                ]
            )
        );
    }

    /**
     * Get current user's participation status in event
     *
     * @param int $eventId Event identifier
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'calendar.meeting.status.get',
        'https://apidocs.bitrix24.com/api-reference/calendar/calendar-event/calendar-meeting-status-get.html',
        "Get current user's participation status in event"
    )]
    public function getMeetingStatus(int $eventId): MeetingStatusResult
    {
        return new MeetingStatusResult(
            $this->core->call(
                'calendar.meeting.status.get',
                [
                    'eventId' => $eventId,
                ]
            )
        );
    }

    /**
     * Set participation status in event for current user
     *
     * @param int $eventId Event identifier
     * @param string $status Participation status (Y/N/Q)
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'calendar.meeting.status.set',
        'https://apidocs.bitrix24.com/api-reference/calendar/calendar-event/calendar-meeting-status-set.html',
        'Set participation status in event for current user'
    )]
    public function setMeetingStatus(int $eventId, string $status): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call(
                'calendar.meeting.status.set',
                [
                    'eventId' => $eventId,
                    'status' => $status,
                ]
            )
        );
    }

    /**
     * Get users' availability from list
     *
     * @param array $users Array of user IDs
     * @param string $from Start date (YYYY-MM-DD)
     * @param string $to End date (YYYY-MM-DD)
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'calendar.accessibility.get',
        'https://apidocs.bitrix24.com/api-reference/calendar/calendar-event/calendar-accessibility-get.html',
        "Get users' availability from list"
    )]
    public function getAccessibility(array $users, string $from, string $to): AccessibilityResult
    {
        return new AccessibilityResult(
            $this->core->call(
                'calendar.accessibility.get',
                [
                    'users' => $users,
                    'from' => $from,
                    'to' => $to,
                ]
            )
        );
    }
}
