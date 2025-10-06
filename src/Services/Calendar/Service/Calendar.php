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

namespace Bitrix24\SDK\Services\Calendar\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Calendar\Result\CalendarSectionAddedResult;
use Bitrix24\SDK\Services\Calendar\Result\CalendarSectionUpdatedResult;
use Bitrix24\SDK\Services\Calendar\Result\CalendarSectionsResult;
use Bitrix24\SDK\Services\Calendar\Result\CalendarSettingsResult;
use Bitrix24\SDK\Services\Calendar\Result\CalendarUserSettingsResult;
use Bitrix24\SDK\Services\Calendar\Result\CalendarUserSettingsSetResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['calendar']))]
class Calendar extends AbstractService
{
    public function __construct(CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Adds a new calendar section.
     *
     * @link https://apidocs.bitrix24.com/api-reference/calendar/calendar-section-add.html
     *
     * @param string $type Calendar type (user or group)
     * @param int $ownerId Calendar owner identifier
     * @param string $name Calendar name
     * @param array $additionalFields Additional fields for creating a calendar section
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'calendar.section.add',
        'https://apidocs.bitrix24.com/api-reference/calendar/calendar-section-add.html',
        'Adds a new calendar section.'
    )]
    public function add(string $type, int $ownerId, string $name, array $additionalFields = []): CalendarSectionAddedResult
    {
        $fields = array_merge([
            'type' => $type,
            'ownerId' => $ownerId,
            'name' => $name
        ], $additionalFields);

        return new CalendarSectionAddedResult(
            $this->core->call('calendar.section.add', $fields)
        );
    }

    /**
     * Updates a calendar section.
     *
     * @link https://apidocs.bitrix24.com/api-reference/calendar/calendar-section-update.html
     *
     * @param string $type Calendar type (user or group)
     * @param int $ownerId Calendar owner identifier
     * @param int $id Calendar section identifier
     * @param array $fields Field values for update
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'calendar.section.update',
        'https://apidocs.bitrix24.com/api-reference/calendar/calendar-section-update.html',
        'Updates a calendar section.'
    )]
    public function update(string $type, int $ownerId, int $id, array $fields = []): CalendarSectionUpdatedResult
    {
        $params = array_merge([
            'type' => $type,
            'ownerId' => $ownerId,
            'id' => $id
        ], $fields);

        return new CalendarSectionUpdatedResult(
            $this->core->call('calendar.section.update', $params)
        );
    }

    /**
     * Returns a list of calendar sections.
     *
     * @link https://apidocs.bitrix24.com/api-reference/calendar/calendar-section-get.html
     *
     * @param string $type Calendar type (user, group, company_calendar, location, etc.)
     * @param int $ownerId Calendar owner identifier
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'calendar.section.get',
        'https://apidocs.bitrix24.com/api-reference/calendar/calendar-section-get.html',
        'Returns a list of calendar sections.'
    )]
    public function get(string $type, int $ownerId): CalendarSectionsResult
    {
        return new CalendarSectionsResult(
            $this->core->call('calendar.section.get', [
                'type' => $type,
                'ownerId' => $ownerId
            ])
        );
    }

    /**
     * Deletes a calendar section.
     *
     * @link https://apidocs.bitrix24.com/api-reference/calendar/calendar-section-delete.html
     *
     * @param string $type Calendar type (user or group)
     * @param int $ownerId Calendar owner identifier
     * @param int $id Calendar section identifier
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'calendar.section.delete',
        'https://apidocs.bitrix24.com/api-reference/calendar/calendar-section-delete.html',
        'Deletes a calendar section.'
    )]
    public function delete(string $type, int $ownerId, int $id): DeletedItemResult
    {
        return new DeletedItemResult(
            $this->core->call('calendar.section.delete', [
                'type' => $type,
                'ownerId' => $ownerId,
                'id' => $id
            ])
        );
    }

    /**
     * Returns main calendar settings.
     *
     * @link https://apidocs.bitrix24.com/api-reference/calendar/calendar-settings-get.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'calendar.settings.get',
        'https://apidocs.bitrix24.com/api-reference/calendar/calendar-settings-get.html',
        'Returns main calendar settings.'
    )]
    public function getSettings(): CalendarSettingsResult
    {
        return new CalendarSettingsResult(
            $this->core->call('calendar.settings.get', [])
        );
    }

    /**
     * Returns user calendar settings.
     *
     * @link https://apidocs.bitrix24.com/api-reference/calendar/calendar-user-settings-get.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'calendar.user.settings.get',
        'https://apidocs.bitrix24.com/api-reference/calendar/calendar-user-settings-get.html',
        'Returns user calendar settings.'
    )]
    public function getUserSettings(): CalendarUserSettingsResult
    {
        return new CalendarUserSettingsResult(
            $this->core->call('calendar.user.settings.get', [])
        );
    }

    /**
     * Sets user calendar settings.
     *
     * @link https://apidocs.bitrix24.com/api-reference/calendar/calendar-user-settings-set.html
     *
     * @param array $settings User calendar settings to set
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'calendar.user.settings.set',
        'https://apidocs.bitrix24.com/api-reference/calendar/calendar-user-settings-set.html',
        'Sets user calendar settings.'
    )]
    public function setUserSettings(array $settings): CalendarUserSettingsSetResult
    {
        return new CalendarUserSettingsSetResult(
            $this->core->call('calendar.user.settings.set', [
                'settings' => $settings
            ])
        );
    }
}
