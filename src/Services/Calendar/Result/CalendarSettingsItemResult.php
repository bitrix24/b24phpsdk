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
 * Class CalendarSettingsItemResult
 * Represents main calendar settings returned by Bitrix24 REST API.
 *
 * Fields and their types are taken from Bitrix24 Calendar API documentation.
 *
 * @property-read string|null $work_time_start Start time of the workday
 * @property-read string|null $work_time_end End time of the workday
 * @property-read string|null $year_holidays List of holidays
 * @property-read string|null $year_workdays List of workdays
 * @property-read array|null $week_holidays Array of weekend days
 * @property-read string|null $week_start Day the week starts
 * @property-read string|null $user_name_template User name template
 * @property-read bool|null $sync_by_push Flag for automatic calendar synchronization via subscription
 * @property-read bool|null $user_show_login Flag for displaying user login
 * @property-read string|null $path_to_user Template link to user profile
 * @property-read string|null $path_to_user_calendar Template link to view user calendar
 * @property-read string|null $path_to_group Template link to view workgroup
 * @property-read string|null $path_to_group_calendar Template link to view group calendar
 * @property-read string|null $path_to_vr Template link to video conference room
 * @property-read string|null $path_to_rm Template link to meeting room
 * @property-read string|null $rm_iblock_type Type of infoblock for booking meeting and video conference rooms
 * @property-read string|null $rm_iblock_id Identifier of the infoblock for booking meeting rooms
 * @property-read bool|null $dep_manager_sub Flag allowing managers to view subordinates' calendars
 * @property-read array|null $denied_superpose_types List of calendar types that cannot be added to favorites
 * @property-read bool|null $pathes_for_sites Sets link templates common for all sites
 * @property-read string|null $forum_id Identifier of the forum for comments
 * @property-read bool|null $rm_for_sites Sets meeting room parameters common for all sites
 * @property-read string|null $path_to_type_company_calendar Template link to view company calendars
 * @property-read string|null $path_to_type_location Template link to view meeting room bookings
 * @property-read string|null $path_to_type_open_event Template link to view open event calendar
 */
class CalendarSettingsItemResult extends AbstractItem
{
}
