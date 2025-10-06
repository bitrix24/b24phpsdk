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

namespace Bitrix24\SDK\Services\Calendar\Event\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * Calendar event item result
 *
 * @property-read string $ID Event identifier
 * @property-read string $PARENT_ID Identifier of the parent event
 * @property-read string $DELETED Flag indicating whether the event is deleted (Y/N)
 * @property-read string $CAL_TYPE Type of calendar in which the event is located
 * @property-read string $OWNER_ID Identifier of the calendar owner
 * @property-read string $NAME Event name
 * @property-read string $DATE_FROM Start date of the event
 * @property-read string $DATE_TO End date of the event
 * @property-read string|null $ORIGINAL_DATE_FROM Start date of the original event for recurring events
 * @property-read string $TZ_FROM Timezone of the event start date
 * @property-read string $TZ_TO Timezone of the event end date
 * @property-read string $TZ_OFFSET_FROM Time offset of the event start time relative to UTC in seconds
 * @property-read string $TZ_OFFSET_TO Time offset of the event end time relative to UTC in seconds
 * @property-read string $DATE_FROM_TS_UTC Start date and time of the event in UTC in timestamp format
 * @property-read string $DATE_TO_TS_UTC End date and time of the event in UTC in timestamp format
 * @property-read string $DT_SKIP_TIME Flag indicating that the event lasts all day (Y/N)
 * @property-read int $DT_LENGTH Duration of the event in seconds
 * @property-read string|null $EVENT_TYPE Type of event
 * @property-read string $CREATED_BY Identifier of the user who created the event
 * @property-read string $DATE_CREATE Date the event was created
 * @property-read string $TIMESTAMP_X Date the event was modified
 * @property-read string $DESCRIPTION Description of the event
 * @property-read string $PRIVATE_EVENT Mark indicating that the event is private (Y/N)
 * @property-read string $ACCESSIBILITY Availability of event participants (busy/absent/quest/free)
 * @property-read string $IMPORTANCE Importance of the event (high/normal/low)
 * @property-read bool $IS_MEETING Indicator of a meeting with event participants
 * @property-read string $MEETING_STATUS Status of participation in the event (Y/N/Q/H)
 * @property-read string $MEETING_HOST Identifier of the user hosting the event
 * @property-read array $MEETING Object describing meeting settings
 * @property-read string $LOCATION Identifier or name of the event location
 * @property-read array $REMIND Array of objects describing event reminders
 * @property-read string $COLOR Background color of the event
 * @property-read array|null $RRULE Recurrence of the event in the form of an object in terms of the iCalendar standard
 * @property-read string $EXDATE List of exception dates from the recurrence rule
 * @property-read string $DAV_XML_ID Synchronization identifier
 * @property-read string $G_EVENT_ID Synchronization identifier
 * @property-read string $CAL_DAV_LABEL Synchronization identifier
 * @property-read string $VERSION Version of event changes
 * @property-read array $ATTENDEES_CODES Identifiers of event participants
 * @property-read string|null $RECURRENCE_ID Identifier of the original event when editing only the current one
 * @property-read array|null $RELATIONS Object for recurring events with information about relationships to the original event
 * @property-read string $SECTION_ID Identifier of the calendar in which the event is located
 * @property-read string|null $SYNC_STATUS Synchronization status of the event
 * @property-read array $UF_CRM_CAL_EVENT Array of identifiers of CRM entities linked to the event
 * @property-read array|bool $UF_WEBDAV_CAL_EVENT Array of identifiers of files linked to the event
 * @property-read string|null $SECTION_DAV_XML_ID Synchronization identifier of the event calendar
 * @property-read string $DATE_FROM_FORMATTED Formatted start date of the event
 * @property-read string $DATE_TO_FORMATTED Formatted end date of the event
 * @property-read string $SECT_ID Identifier of the calendar in which the event is located
 * @property-read array $ATTENDEE_LIST Array of objects describing event participants and their participation statuses
 * @property-read int|null $COLLAB_ID Identifier of the collaboration in which the event was created
 * @property-read array $attendeesEntityList Array of objects describing users — event participants
 */
class EventItemResult extends AbstractItem
{
}
