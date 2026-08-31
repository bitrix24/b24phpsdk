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

namespace Bitrix24\SDK\Tests\Unit\Services\Timeman\Service;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Services\Timeman\Result\TimemanSettingsResult;
use Bitrix24\SDK\Services\Timeman\Result\WorkdayResult;
use Bitrix24\SDK\Services\Timeman\Service\Timeman;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(Timeman::class)]
class TimemanTest extends TestCase
{
    #[TestDox('Test Timeman service can be instantiated')]
    public function testCanBeInstantiated(): void
    {
        $core = $this->createStub(CoreInterface::class);
        $timeman = new Timeman($core, new NullLogger());

        $this->assertInstanceOf(Timeman::class, $timeman);
    }

    #[TestDox('Test Timeman::open calls timeman.open without parameters')]
    public function testOpenCallsCorrectMethodWithNoParams(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $response = $this->createStub(Response::class);

        $core->expects($this->once())
            ->method('call')
            ->with('timeman.open', [])
            ->willReturn($response);

        $workdayResult = (new Timeman($core, new NullLogger()))->open();

        $this->assertInstanceOf(WorkdayResult::class, $workdayResult);
    }

    #[TestDox('Test Timeman::open builds correct parameters')]
    public function testOpenBuildsCorrectParameters(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $response = $this->createStub(Response::class);
        $time = CarbonImmutable::create(2025, 3, 27, 8, 0, 1, 'UTC');

        $core->expects($this->once())
            ->method('call')
            ->with(
                'timeman.open',
                [
                    'USER_ID' => 503,
                    'TIME' => $time->format(CarbonImmutable::ATOM),
                    'REPORT' => 'Test report',
                    'LAT' => 53.548841,
                    'LON' => 9.987274,
                ]
            )
            ->willReturn($response);

        $workdayResult = (new Timeman($core, new NullLogger()))->open(
            userId: 503,
            time: $time,
            report: 'Test report',
            lat: 53.548841,
            lon: 9.987274
        );

        $this->assertInstanceOf(WorkdayResult::class, $workdayResult);
    }

    #[TestDox('Test Timeman::pause calls timeman.pause without parameters')]
    public function testPauseCallsCorrectMethodWithNoParams(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $response = $this->createStub(Response::class);

        $core->expects($this->once())
            ->method('call')
            ->with('timeman.pause', [])
            ->willReturn($response);

        $workdayResult = (new Timeman($core, new NullLogger()))->pause();

        $this->assertInstanceOf(WorkdayResult::class, $workdayResult);
    }

    #[TestDox('Test Timeman::pause builds correct parameters')]
    public function testPauseBuildsCorrectParameters(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $response = $this->createStub(Response::class);

        $core->expects($this->once())
            ->method('call')
            ->with('timeman.pause', ['USER_ID' => 503])
            ->willReturn($response);

        $workdayResult = (new Timeman($core, new NullLogger()))->pause(userId: 503);

        $this->assertInstanceOf(WorkdayResult::class, $workdayResult);
    }

    #[TestDox('Test Timeman::close calls timeman.close without parameters')]
    public function testCloseCallsCorrectMethodWithNoParams(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $response = $this->createStub(Response::class);

        $core->expects($this->once())
            ->method('call')
            ->with('timeman.close', [])
            ->willReturn($response);

        $workdayResult = (new Timeman($core, new NullLogger()))->close();

        $this->assertInstanceOf(WorkdayResult::class, $workdayResult);
    }

    #[TestDox('Test Timeman::close builds correct parameters')]
    public function testCloseBuildsCorrectParameters(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $response = $this->createStub(Response::class);
        $time = CarbonImmutable::create(2025, 3, 27, 17, 0, 0, 'UTC');

        $core->expects($this->once())
            ->method('call')
            ->with(
                'timeman.close',
                [
                    'USER_ID' => 503,
                    'TIME' => $time->format(CarbonImmutable::ATOM),
                    'REPORT' => 'End of day',
                    'LAT' => 53.548841,
                    'LON' => 9.987274,
                ]
            )
            ->willReturn($response);

        $workdayResult = (new Timeman($core, new NullLogger()))->close(
            userId: 503,
            time: $time,
            report: 'End of day',
            lat: 53.548841,
            lon: 9.987274
        );

        $this->assertInstanceOf(WorkdayResult::class, $workdayResult);
    }

    #[TestDox('Test Timeman::status calls timeman.status without parameters')]
    public function testStatusCallsCorrectMethodWithNoParams(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $response = $this->createStub(Response::class);

        $core->expects($this->once())
            ->method('call')
            ->with('timeman.status', [])
            ->willReturn($response);

        $workdayResult = (new Timeman($core, new NullLogger()))->status();

        $this->assertInstanceOf(WorkdayResult::class, $workdayResult);
    }

    #[TestDox('Test Timeman::status builds correct parameters')]
    public function testStatusBuildsCorrectParameters(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $response = $this->createStub(Response::class);

        $core->expects($this->once())
            ->method('call')
            ->with('timeman.status', ['USER_ID' => 503])
            ->willReturn($response);

        $workdayResult = (new Timeman($core, new NullLogger()))->status(userId: 503);

        $this->assertInstanceOf(WorkdayResult::class, $workdayResult);
    }

    #[TestDox('Test Timeman::settings calls timeman.settings without parameters')]
    public function testSettingsCallsCorrectMethodWithNoParams(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $response = $this->createStub(Response::class);

        $core->expects($this->once())
            ->method('call')
            ->with('timeman.settings', [])
            ->willReturn($response);

        $timemanSettingsResult = (new Timeman($core, new NullLogger()))->settings();

        $this->assertInstanceOf(TimemanSettingsResult::class, $timemanSettingsResult);
    }

    #[TestDox('Test Timeman::settings builds correct parameters')]
    public function testSettingsBuildsCorrectParameters(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $response = $this->createStub(Response::class);

        $core->expects($this->once())
            ->method('call')
            ->with('timeman.settings', ['USER_ID' => 503])
            ->willReturn($response);

        $timemanSettingsResult = (new Timeman($core, new NullLogger()))->settings(userId: 503);

        $this->assertInstanceOf(TimemanSettingsResult::class, $timemanSettingsResult);
    }
}
