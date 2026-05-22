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

namespace Bitrix24\SDK\Tests\Integration\Services\Timeman\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Timeman\Service\Timeman;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Timeman::class)]
class TimemanTest extends TestCase
{
    private Timeman $timemanService;

    #[\Override]
    protected function setUp(): void
    {
        $this->timemanService = Factory::getServiceBuilder()->getTimemanScope()->timeman();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('timeman.status returns current workday information')]
    public function testStatus(): void
    {
        $workdayResult = $this->timemanService->status();
        $workdayItemResult = $workdayResult->getWorkday();

        $this->assertContains(
            $workdayItemResult->STATUS,
            ['OPENED', 'CLOSED', 'PAUSED', 'EXPIRED'],
            'STATUS must be one of OPENED, CLOSED, PAUSED, EXPIRED'
        );
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('timeman.settings returns user work time settings')]
    public function testSettings(): void
    {
        $timemanSettingsResult = $this->timemanService->settings();
        $timemanSettingsItemResult = $timemanSettingsResult->getSettings();

        $this->assertIsBool($timemanSettingsItemResult->UF_TIMEMAN);
        $this->assertIsBool($timemanSettingsItemResult->UF_TM_FREE);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('timeman.open opens the workday for the current user')]
    public function testOpen(): void
    {
        $workdayResult = $this->timemanService->open();
        $workdayItemResult = $workdayResult->getWorkday();

        $this->assertContains(
            $workdayItemResult->STATUS,
            ['OPENED', 'CLOSED', 'PAUSED', 'EXPIRED'],
            'STATUS must be one of OPENED, CLOSED, PAUSED, EXPIRED'
        );
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('timeman.pause pauses the current workday')]
    public function testPause(): void
    {
        // Ensure workday is open before pausing
        $this->timemanService->open();

        $workdayResult = $this->timemanService->pause();
        $workdayItemResult = $workdayResult->getWorkday();

        $this->assertContains(
            $workdayItemResult->STATUS,
            ['OPENED', 'CLOSED', 'PAUSED', 'EXPIRED'],
            'STATUS must be one of OPENED, CLOSED, PAUSED, EXPIRED'
        );
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('timeman.close closes the current workday')]
    public function testClose(): void
    {
        // Ensure workday is open before closing
        $this->timemanService->open();

        $workdayResult = $this->timemanService->close();
        $workdayItemResult = $workdayResult->getWorkday();

        $this->assertContains(
            $workdayItemResult->STATUS,
            ['OPENED', 'CLOSED', 'PAUSED', 'EXPIRED'],
            'STATUS must be one of OPENED, CLOSED, PAUSED, EXPIRED'
        );
    }
}
