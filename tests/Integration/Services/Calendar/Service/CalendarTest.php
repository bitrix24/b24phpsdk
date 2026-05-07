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

namespace Bitrix24\SDK\Tests\Integration\Services\Calendar\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Calendar\Result\CalendarSectionItemResult;
use Bitrix24\SDK\Services\Calendar\Service\Calendar;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

/**
 * Class CalendarTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Calendar\Service
 */
#[CoversMethod(Calendar::class,'add')]
#[CoversMethod(Calendar::class,'update')]
#[CoversMethod(Calendar::class,'get')]
#[CoversMethod(Calendar::class,'delete')]
#[CoversMethod(Calendar::class,'getSettings')]
#[CoversMethod(Calendar::class,'getUserSettings')]
#[CoversMethod(Calendar::class,'setUserSettings')]
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\Calendar\Service\Calendar::class)]
class CalendarTest extends TestCase
{
    use CustomBitrix24Assertions;

    protected Calendar $calendarService;

    protected int $currentUserId;

    protected array $createdCalendarIds = [];

    #[\Override]
    protected function setUp(): void
    {
        $serviceBuilder = Factory::getServiceBuilder();
        $this->calendarService = $serviceBuilder->getCalendarScope()->calendar();
        $this->currentUserId = $this->getCurrentUserId();
    }

    #[\Override]
    protected function tearDown(): void
    {
        // Clean up created calendar sections
        foreach ($this->createdCalendarIds as $createdCalendarId) {
            try {
                $this->calendarService->delete('user', $this->currentUserId, $createdCalendarId);
            } catch (\Exception) {
                // Ignore if calendar doesn't exist
            }
        }
    }

    /**
     * Helper method to get current user ID
     */
    protected function getCurrentUserId(): int
    {
        $core = Factory::getCore();
        $response = $core->call('user.current', []);
        return (int)$response->getResponseData()->getResult()['ID'];
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAdd(): void
    {
        // Create a calendar section
        $calendarName = 'Test Calendar ' . time();
        $calendarFields = [
            'description' => 'Test calendar description',
            'color' => '#9cbeee',
            'text_color' => '#283000'
        ];

        $calendarSectionAddedResult = $this->calendarService->add('user', $this->currentUserId, $calendarName, $calendarFields);
        $calendarId = $calendarSectionAddedResult->getId();

        self::assertGreaterThan(0, $calendarId);
        $this->createdCalendarIds[] = $calendarId;

        // Verify the calendar was created by retrieving it
        $calendarSectionsResult = $this->calendarService->get('user', $this->currentUserId);
        $sections = $calendarSectionsResult->getSections();

        $found = false;
        foreach ($sections as $section) {
            if (intval($section->ID) === $calendarId) {
                $found = true;
                self::assertEquals($calendarName, $section->NAME);
                self::assertEquals('Test calendar description', $section->DESCRIPTION);
                self::assertEquals('#9cbeee', $section->COLOR);
                break;
            }
        }

        self::assertTrue($found, 'Created calendar section not found in list');
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testUpdate(): void
    {
        // Create a calendar section first
        $calendarName = 'Test Calendar for Update ' . time();
        $calendarSectionAddedResult = $this->calendarService->add('user', $this->currentUserId, $calendarName);
        $calendarId = $calendarSectionAddedResult->getId();
        $this->createdCalendarIds[] = $calendarId;

        // Update the calendar section
        $updatedName = 'Updated Calendar Name ' . time();
        $updateFields = [
            'name' => $updatedName,
            'description' => 'Updated description',
            'color' => '#ff0000'
        ];

        $calendarSectionUpdatedResult = $this->calendarService->update('user', $this->currentUserId, $calendarId, $updateFields);
        self::assertEquals($calendarId, $calendarSectionUpdatedResult->getId());

        // Verify the calendar was updated
        $calendarSectionsResult = $this->calendarService->get('user', $this->currentUserId);
        $sections = $calendarSectionsResult->getSections();

        $found = false;
        foreach ($sections as $section) {
            if (intval($section->ID) === $calendarId) {
                $found = true;
                self::assertEquals($updatedName, $section->NAME);
                self::assertEquals('Updated description', $section->DESCRIPTION);
                self::assertEquals('#ff0000', $section->COLOR);
                break;
            }
        }

        self::assertTrue($found, 'Updated calendar section not found in list');
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGet(): void
    {
        // Create a test calendar section
        $calendarName = 'Test Calendar for Get ' . time();
        $calendarSectionAddedResult = $this->calendarService->add('user', $this->currentUserId, $calendarName);
        $calendarId = $calendarSectionAddedResult->getId();
        $this->createdCalendarIds[] = $calendarId;

        // Get calendar sections
        $calendarSectionsResult = $this->calendarService->get('user', $this->currentUserId);
        $sections = $calendarSectionsResult->getSections();

        self::assertIsArray($sections);
        self::assertNotEmpty($sections);

        // Verify our created calendar is in the list
        $found = false;
        foreach ($sections as $section) {
            self::assertInstanceOf(CalendarSectionItemResult::class, $section);
            if (intval($section->ID) === $calendarId) {
                $found = true;
                self::assertEquals($calendarName, $section->NAME);
                self::assertEquals('user', $section->CAL_TYPE);
                self::assertEquals($this->currentUserId, $section->OWNER_ID);
                break;
            }
        }

        self::assertTrue($found, 'Created calendar section not found in list');
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelete(): void
    {
        // Create a calendar section first
        $calendarName = 'Test Calendar for Delete ' . time();
        $calendarSectionAddedResult = $this->calendarService->add('user', $this->currentUserId, $calendarName);
        $calendarId = $calendarSectionAddedResult->getId();

        // Delete the calendar section
        $deletedItemResult = $this->calendarService->delete('user', $this->currentUserId, $calendarId);
        self::assertTrue($deletedItemResult->isSuccess());

        // Verify the calendar was deleted by checking it's not in the list
        $calendarSectionsResult = $this->calendarService->get('user', $this->currentUserId);
        $sections = $calendarSectionsResult->getSections();

        $found = false;
        foreach ($sections as $section) {
            if (intval($section->ID) === $calendarId) {
                $found = true;
                break;
            }
        }

        self::assertFalse($found, 'Calendar section should have been deleted');
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetSettings(): void
    {
        $calendarSettingsResult = $this->calendarService->getSettings();
        $calendarSettingsItemResult = $calendarSettingsResult->getSettings();

        self::assertNotNull($calendarSettingsItemResult);
        self::assertNotNull($calendarSettingsItemResult->work_time_start);
        self::assertNotNull($calendarSettingsItemResult->work_time_end);
        self::assertNotNull($calendarSettingsItemResult->week_holidays);
        self::assertNotNull($calendarSettingsItemResult->week_start);
        self::assertNotNull($calendarSettingsItemResult->user_name_template);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetUserSettings(): void
    {
        $calendarUserSettingsResult = $this->calendarService->getUserSettings();
        $calendarUserSettingsItemResult = $calendarUserSettingsResult->getSettings();

        self::assertNotNull($calendarUserSettingsItemResult);
        self::assertNotNull($calendarUserSettingsItemResult->view);
        self::assertNotNull($calendarUserSettingsItemResult->showDeclined);
        self::assertNotNull($calendarUserSettingsItemResult->showTasks);
        self::assertNotNull($calendarUserSettingsItemResult->timezoneName);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testSetUserSettings(): void
    {
        // Get current settings first
        $calendarUserSettingsItemResult = $this->calendarService->getUserSettings()->getSettings();

        // Prepare new settings
        $newSettings = [
            'view' => 'week',
            'showDeclined' => false,
            'showTasks' => 'Y',
            'collapseOffHours' => 'Y'
        ];

        // Set new settings
        $calendarUserSettingsSetResult = $this->calendarService->setUserSettings($newSettings);
        self::assertTrue($calendarUserSettingsSetResult->isSuccess());

        // Verify settings were updated
        $updatedSettings = $this->calendarService->getUserSettings()->getSettings();

        self::assertEquals('week', $updatedSettings->view);
        self::assertEquals(false, $updatedSettings->showDeclined);
        self::assertEquals('Y', $updatedSettings->showTasks);
        self::assertEquals('Y', $updatedSettings->collapseOffHours);

        // Restore original settings
        $restoreSettings = [
            'view' => $calendarUserSettingsItemResult->view ?? 'month',
            'showDeclined' => $calendarUserSettingsItemResult->showDeclined ?? true,
            'showTasks' => $calendarUserSettingsItemResult->showTasks ?? 'Y',
            'collapseOffHours' => $calendarUserSettingsItemResult->collapseOffHours ?? 'N'
        ];

        $this->calendarService->setUserSettings($restoreSettings);
    }
}