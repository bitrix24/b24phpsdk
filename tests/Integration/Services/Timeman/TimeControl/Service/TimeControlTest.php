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

namespace Bitrix24\SDK\Tests\Integration\Services\Timeman\TimeControl\Service;

use Bitrix24\SDK\Services\Timeman\TimeControl\Service\TimeControl;
use Bitrix24\SDK\Tests\Integration\Services\Timeman\ReadOnlyCoreTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(TimeControl::class)]
class TimeControlTest extends TestCase
{
    use ReadOnlyCoreTrait;
    public function testGetSettings(): void
    {
        $timeControl = new TimeControl($this->getReadOnlyCore(), new NullLogger());
        $result = $timeControl->getSettings();
        self::assertIsBool($result->getSettings()->active);
    }

    public function testGetReportSettings(): void
    {
        $timeControl = new TimeControl($this->getReadOnlyCore(), new NullLogger());
        $result = $timeControl->getReportSettings();
        self::assertIsInt($result->getSettings()->user_id);
    }

    public function testGetReportUsers(): void
    {
        $timeControl = new TimeControl($this->getReadOnlyCore(), new NullLogger());
        $result = $timeControl->getReportUsers();
        self::assertIsArray($result->getUsers());
    }

    public function testGetReports(): void
    {
        $timeControl = new TimeControl($this->getReadOnlyCore(), new NullLogger());
        $result = $timeControl->getReports($timeControl->getReportSettings()->getSettings()->user_id, (int) date('n'), (int) date('Y'));
        self::assertIsArray($result->getReport()->days);
    }

}
