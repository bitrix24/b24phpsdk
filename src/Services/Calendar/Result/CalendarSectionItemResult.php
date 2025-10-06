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

namespace Bitrix24\SDK\Services\Calendar\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;
use Carbon\CarbonImmutable;

/**
 * Class CalendarSectionItemResult
 * Represents a single calendar section item returned by Bitrix24 REST API.
 *
 * Fields and their types are taken from Bitrix24 Calendar API documentation.
 *
 * @property-read int|null $ID Calendar section identifier
 * @property-read string|null $NAME Calendar section name
 * @property-read string|null $GAPI_CALENDAR_ID Google calendar synchronization identifier
 * @property-read string|null $DESCRIPTION Calendar section description
 * @property-read string|null $COLOR Calendar section color
 * @property-read string|null $TEXT_COLOR Text color in the calendar section
 * @property-read array|null $EXPORT Object with calendar export parameters
 * @property-read string|null $CAL_TYPE Calendar type (user, group, company_calendar, location)
 * @property-read string|null $OWNER_ID Calendar owner identifier
 * @property-read string|null $CREATED_BY Calendar creator identifier
 * @property-read CarbonImmutable|null $DATE_CREATE Calendar creation date
 * @property-read CarbonImmutable|null $TIMESTAMP_X Calendar modification date
 * @property-read string|null $CAL_DAV_CON Synchronization identifier
 * @property-read string|null $SYNC_TOKEN Synchronization identifier
 * @property-read string|null $PAGE_TOKEN Synchronization identifier
 * @property-read string|null $EXTERNAL_TYPE Provider type for synchronization
 * @property-read array|null $ACCESS Object containing access data for the calendar
 * @property-read bool|null $IS_COLLAB Flag indicating whether the calendar belongs to collaboration
 * @property-read array|null $PERM Object access permissions for the current user to the calendar
 */
class CalendarSectionItemResult extends AbstractItem
{
}
