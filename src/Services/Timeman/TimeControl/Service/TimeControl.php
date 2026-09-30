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

namespace Bitrix24\SDK\Services\Timeman\TimeControl\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Timeman\TimeControl\Result\ReportSettingsResult;
use Bitrix24\SDK\Services\Timeman\TimeControl\Result\ReportUsersResult;
use Bitrix24\SDK\Services\Timeman\TimeControl\Result\ReportsResult;
use Bitrix24\SDK\Services\Timeman\TimeControl\Result\TimeControlSettingsResult;
use Bitrix24\SDK\Core\Result\UpdatedItemResult;

#[ApiServiceMetadata(new Scope(['timeman']))]
class TimeControl extends AbstractService
{
    /**
     * Adds an explanation to an existing detected absence.
     * Pass a REPORT_ID obtained from getReports(); this does not create an absence.
     */
    #[ApiEndpointMetadata('timeman.timecontrol.report.add', 'https://apidocs.bitrix24.com/api-reference/timeman/timecontrol/timeman-timecontrol-report-add.html', 'Adds an explanation to an existing detected absence.')]
    public function addReport(int $reportId, string $text, ?int $userId = null, ?string $type = null, ?bool $calendar = null): UpdatedItemResult
    {
        $params = array_filter(['REPORT_ID' => $reportId, 'TEXT' => $text, 'USER_ID' => $userId, 'TYPE' => $type, 'CALENDAR' => $calendar === null ? null : ($calendar ? 'Y' : 'N')], static fn ($value): bool => $value !== null);

        return new UpdatedItemResult($this->core->call('timeman.timecontrol.report.add', $params));
    }

    /**
     * Returns detected absences for a user and month.
     */
    #[ApiEndpointMetadata('timeman.timecontrol.reports.get', 'https://apidocs.bitrix24.com/api-reference/timeman/timecontrol/timeman-timecontrol-reports-get.html', 'Returns detected absences for a user and month.')]
    public function getReports(int $userId, int $month, int $year, ?int $idleMinutes = null, ?int $workdayHours = null): ReportsResult
    {
        $params = array_filter(['USER_ID' => $userId, 'MONTH' => $month, 'YEAR' => $year, 'IDLE_MINUTES' => $idleMinutes, 'WORKDAY_HOURS' => $workdayHours], static fn ($value): bool => $value !== null);

        return new ReportsResult($this->core->call('timeman.timecontrol.reports.get', $params));
    }

    /**
     * Returns role-dependent report interface settings.
     */
    #[ApiEndpointMetadata('timeman.timecontrol.reports.settings.get', 'https://apidocs.bitrix24.com/api-reference/timeman/timecontrol/timeman-timecontrol-reports-settings-get.html', 'Returns role-dependent report interface settings.')]
    public function getReportSettings(): ReportSettingsResult
    {
        $params = [];

        return new ReportSettingsResult($this->core->call('timeman.timecontrol.reports.settings.get', $params));
    }

    /**
     * Returns the users visible to the current report viewer.
     */
    #[ApiEndpointMetadata('timeman.timecontrol.reports.users.get', 'https://apidocs.bitrix24.com/api-reference/timeman/timecontrol/timeman-timecontrol-reports-users-get.html', 'Returns the users visible to the current report viewer.')]
    public function getReportUsers(?int $departmentId = null): ReportUsersResult
    {
        $params = $departmentId === null ? [] : ['DEPARTMENT_ID' => $departmentId];

        return new ReportUsersResult($this->core->call('timeman.timecontrol.reports.users.get', $params));
    }

    /**
     * Returns global time-control settings.
     */
    #[ApiEndpointMetadata('timeman.timecontrol.settings.get', 'https://apidocs.bitrix24.com/api-reference/timeman/timecontrol/timeman-timecontrol-settings-get.html', 'Returns global time-control settings.')]
    public function getSettings(): TimeControlSettingsResult
    {
        $params = [];

        return new TimeControlSettingsResult($this->core->call('timeman.timecontrol.settings.get', $params));
    }

    /**
     * Updates global time-control settings.
     * @param array{ACTIVE?: bool|int, MINIMUM_IDLE_FOR_REPORT?: int, REGISTER_OFFLINE?: bool, REGISTER_IDLE?: bool, REGISTER_DESKTOP?: bool, REPORT_REQUEST_TYPE?: string, REPORT_REQUEST_USERS?: int[], REPORT_SIMPLE_TYPE?: string, REPORT_SIMPLE_USERS?: int[], REPORT_FULL_TYPE?: string, REPORT_FULL_USERS?: int[]} $settings
     * ACTIVE=false is encoded as 0 because the API does not disable tracking for false.
     */
    #[ApiEndpointMetadata('timeman.timecontrol.settings.set', 'https://apidocs.bitrix24.com/api-reference/timeman/timecontrol/timeman-timecontrol-settings-set.html', 'Updates global time-control settings.')]
    public function setSettings(array $settings): UpdatedItemResult
    {
        $params = $settings;
        if (array_key_exists('ACTIVE', $params) && is_bool($params['ACTIVE'])) {
            $params['ACTIVE'] = (int) $params['ACTIVE'];
        }

        return new UpdatedItemResult($this->core->call('timeman.timecontrol.settings.set', $params));
    }

}
