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

namespace Bitrix24\SDK\Services\Timeman\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Timeman\Result\TimemanSettingsResult;
use Bitrix24\SDK\Services\Timeman\Result\WorkdayResult;
use Carbon\CarbonImmutable;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['timeman']))]
class Timeman extends AbstractService
{
    public function __construct(CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Opens a new workday or continues a workday after a pause or completion.
     *
     * @link https://apidocs.bitrix24.com/api-reference/timeman/base/timeman-open.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'timeman.open',
        'https://apidocs.bitrix24.com/api-reference/timeman/base/timeman-open.html',
        'Opens a new workday or continues a workday after a pause or completion.'
    )]
    public function open(
        ?int $userId = null,
        ?CarbonImmutable $time = null,
        ?string $report = null,
        ?float $lat = null,
        ?float $lon = null
    ): WorkdayResult {
        $params = [];
        if ($userId !== null) {
            $params['USER_ID'] = $userId;
        }

        if ($time instanceof CarbonImmutable) {
            $params['TIME'] = $time->format(CarbonImmutable::ATOM);
        }

        if ($report !== null) {
            $params['REPORT'] = $report;
        }

        if ($lat !== null) {
            $params['LAT'] = $lat;
        }

        if ($lon !== null) {
            $params['LON'] = $lon;
        }

        return new WorkdayResult($this->core->call('timeman.open', $params));
    }

    /**
     * Pauses the current workday.
     *
     * @link https://apidocs.bitrix24.com/api-reference/timeman/base/timeman-pause.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'timeman.pause',
        'https://apidocs.bitrix24.com/api-reference/timeman/base/timeman-pause.html',
        'Pauses the current workday.'
    )]
    public function pause(?int $userId = null): WorkdayResult
    {
        $params = [];
        if ($userId !== null) {
            $params['USER_ID'] = $userId;
        }

        return new WorkdayResult($this->core->call('timeman.pause', $params));
    }

    /**
     * Closes the current workday.
     *
     * @link https://apidocs.bitrix24.com/api-reference/timeman/base/timeman-close.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'timeman.close',
        'https://apidocs.bitrix24.com/api-reference/timeman/base/timeman-close.html',
        'Closes the current workday.'
    )]
    public function close(
        ?int $userId = null,
        ?CarbonImmutable $time = null,
        ?string $report = null,
        ?float $lat = null,
        ?float $lon = null
    ): WorkdayResult {
        $params = [];
        if ($userId !== null) {
            $params['USER_ID'] = $userId;
        }

        if ($time instanceof CarbonImmutable) {
            $params['TIME'] = $time->format(CarbonImmutable::ATOM);
        }

        if ($report !== null) {
            $params['REPORT'] = $report;
        }

        if ($lat !== null) {
            $params['LAT'] = $lat;
        }

        if ($lon !== null) {
            $params['LON'] = $lon;
        }

        return new WorkdayResult($this->core->call('timeman.close', $params));
    }

    /**
     * Gets information about the current workday of the user.
     *
     * @link https://apidocs.bitrix24.com/api-reference/timeman/base/timeman-status.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'timeman.status',
        'https://apidocs.bitrix24.com/api-reference/timeman/base/timeman-status.html',
        'Gets information about the current workday of the user.'
    )]
    public function status(?int $userId = null): WorkdayResult
    {
        $params = [];
        if ($userId !== null) {
            $params['USER_ID'] = $userId;
        }

        return new WorkdayResult($this->core->call('timeman.status', $params));
    }

    /**
     * Gets the work time settings of the user.
     *
     * @link https://apidocs.bitrix24.com/api-reference/timeman/base/timeman-settings.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'timeman.settings',
        'https://apidocs.bitrix24.com/api-reference/timeman/base/timeman-settings.html',
        'Gets the work time settings of the user.'
    )]
    public function settings(?int $userId = null): TimemanSettingsResult
    {
        $params = [];
        if ($userId !== null) {
            $params['USER_ID'] = $userId;
        }

        return new TimemanSettingsResult($this->core->call('timeman.settings', $params));
    }
}

