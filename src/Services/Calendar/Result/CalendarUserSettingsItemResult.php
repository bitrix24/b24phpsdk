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

/**
 * Class CalendarUserSettingsItemResult
 * Represents user calendar settings returned by Bitrix24 REST API.
 *
 * Fields and their types are taken from Bitrix24 Calendar API documentation.
 *
 * @property-read string|null $view Standard view for the calendar (day, week, month, list)
 * @property-read string|null $meetSection Calendar for invitations
 * @property-read string|null $crmSection Calendar for CRM
 * @property-read bool|null $showDeclined Show events where the user declined to participate
 * @property-read bool|null $denyBusyInvitation Prevent inviting to an event if the time is busy
 * @property-read string|null $collapseOffHours Hide non-working hours in the calendar (Y/N)
 * @property-read string|null $showWeekNumbers Show week numbers (Y/N)
 * @property-read string|null $showTasks Display tasks in the calendar (Y/N)
 * @property-read string|null $syncTasks Synchronize task calendar (Y/N)
 * @property-read string|null $showCompletedTasks Display completed tasks (Y/N)
 * @property-read string|null $lastUsedSection Identifier of the calendar used when creating events
 * @property-read string|null $sendFromEmail E-mail for sending mail invitations
 * @property-read array|null $defaultSections Settings for preset calendars
 * @property-read string|null $syncPeriodPast Number of months for synchronization in the past period
 * @property-read string|null $syncPeriodFuture Number of months for synchronization in the future period
 * @property-read array|null $defaultReminders Object with standard settings for event reminders
 * @property-read string|null $timezoneName Calendar timezone
 * @property-read int|null $timezoneOffsetUTC Timezone offset relative to UTC in seconds
 * @property-read string|null $timezoneDefaultName Default timezone name if timezoneName is not set
 * @property-read string|null $work_time_start Start time of the workday
 * @property-read string|null $work_time_end End time of the workday
 */
class CalendarUserSettingsItemResult extends AbstractItem
{
}
