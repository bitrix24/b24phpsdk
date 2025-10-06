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

namespace Bitrix24\SDK\Services\Calendar\Resource\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * ResourceItemResult represents a calendar resource or booking item.
 *
 * Resource object properties (for calendar.resource.list):
 * @property-read string $ID Resource identifier
 * @property-read string $NAME Resource name
 * @property-read string $CREATED_BY Identifier of the user who created the resource
 *
 * Booking object properties (for calendar.resource.booking.list):
 * @property-read string $ID Booking identifier
 * @property-read string $PARENT_ID For a booking object, always equal to the ID field
 * @property-read string $DELETED Flag indicating whether the booking is deleted. Possible values: Y — booking deleted, N — booking not deleted
 * @property-read string $CAL_TYPE Type of calendar in which the booking is located
 * @property-read string $OWNER_ID For a booking object, always equals '0'
 * @property-read string $NAME Name of the booking
 * @property-read string $DATE_FROM Start date of the booking
 * @property-read string $DATE_TO End date of the booking
 * @property-read string $TZ_FROM Timezone of the start date of the booking
 * @property-read string $TZ_TO Timezone of the end date of the booking
 * @property-read string $TZ_OFFSET_FROM Time offset of the start of the booking relative to UTC in seconds
 * @property-read string $TZ_OFFSET_TO Time offset of the end of the booking relative to UTC in seconds
 * @property-read string $DATE_FROM_TS_UTC Start date and time of the booking in UTC in timestamp format
 * @property-read string $DATE_TO_TS_UTC End date and time of the booking in UTC in timestamp format
 * @property-read string $DT_SKIP_TIME Flag indicating whether the booking lasts all day. Possible values: Y — all day, N — not all day
 * @property-read int $DT_LENGTH Duration of the booking in seconds
 * @property-read string $EVENT_TYPE Type of booking
 * @property-read string $CREATED_BY Identifier of the user who created the booking
 * @property-read string $DATE_CREATE Creation date of the booking
 * @property-read string $TIMESTAMP_X Date of modification of the booking
 * @property-read string $DESCRIPTION Description of the booking
 * @property-read bool $IS_MEETING For a booking object, always false
 * @property-read string $MEETING_STATUS For a booking object, always 'Y'
 * @property-read string $MEETING_HOST For a booking object, always '0'
 * @property-read string $VERSION Version of booking changes
 * @property-read string $SECTION_ID Identifier of the resource in which the booking is located
 * @property-read string $DATE_FROM_FORMATTED Formatted start date of the booking
 * @property-read string $DATE_TO_FORMATTED Formatted end date of the booking
 * @property-read string $SECT_ID Identifier of the resource in which the booking is located
 * @property-read int $RESOURCE_BOOKING_ID Booking identifier
 */
class ResourceItemResult extends AbstractItem
{
}
