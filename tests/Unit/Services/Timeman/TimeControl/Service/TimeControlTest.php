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

namespace Bitrix24\SDK\Tests\Unit\Services\Timeman\TimeControl\Service;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Core\Response\DTO\ResponseData;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Log\NullLogger;
use Bitrix24\SDK\Services\Timeman\TimeControl\Service\TimeControl;

#[CoversClass(TimeControl::class)]
class TimeControlTest extends TestCase
{
    public function testAddReport(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $response = $this->createStub(Response::class);
        $data = $this->createStub(ResponseData::class);
        $data->method('getResult')->willReturn([true]);
        $response->method('getResponseData')->willReturn($data);
        $core->expects(self::once())->method('call')->with('timeman.timecontrol.report.add', self::identicalTo(['REPORT_ID' => 27, 'TEXT' => 'Meeting', 'USER_ID' => 503, 'TYPE' => 'WORK', 'CALENDAR' => 'N']))->willReturn($response);
        $result = (new TimeControl($core, new NullLogger()))->addReport(27, 'Meeting', 503, 'WORK', false);
        self::assertTrue($result->isSuccess());
    }

    public function testAddReportDefaults(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $response = $this->createStub(Response::class);
        $data = $this->createStub(ResponseData::class);
        $data->method('getResult')->willReturn([true]);
        $response->method('getResponseData')->willReturn($data);
        $core->expects(self::once())->method('call')->with('timeman.timecontrol.report.add', self::identicalTo(['REPORT_ID' => 27, 'TEXT' => 'Meeting']))->willReturn($response);
        $result = (new TimeControl($core, new NullLogger()))->addReport(27, 'Meeting');
        self::assertTrue($result->isSuccess());
    }

    public function testGetReports(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $response = $this->createStub(Response::class);
        $data = $this->createStub(ResponseData::class);
        $data->method('getResult')->willReturn(['report' => ['month_title' => 'May', 'date_start' => '2025-05-01T00:00:00+03:00', 'date_finish' => '2025-05-31T23:59:59+03:00', 'days' => [['reports' => [['system_text' => null]]]]], 'user' => ['id' => '503']]);
        $response->method('getResponseData')->willReturn($data);
        $core->expects(self::once())->method('call')->with('timeman.timecontrol.reports.get', self::identicalTo(['USER_ID' => 503, 'MONTH' => 5, 'YEAR' => 2025, 'IDLE_MINUTES' => 0, 'WORKDAY_HOURS' => 8]))->willReturn($response);
        $result = (new TimeControl($core, new NullLogger()))->getReports(503, 5, 2025, 0, 8);
        self::assertNull($result->getReport()->days[0]['reports'][0]['system_text']);
        self::assertSame(503, $result->getUser()->id);
        self::assertInstanceOf(\Carbon\CarbonImmutable::class, $result->getReport()->date_start);
    }

    public function testGetReportsEmpty(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $response = $this->createStub(Response::class);
        $data = $this->createStub(ResponseData::class);
        $data->method('getResult')->willReturn(['report' => ['days' => []], 'user' => ['id' => 503]]);
        $response->method('getResponseData')->willReturn($data);
        $core->expects(self::once())->method('call')->with('timeman.timecontrol.reports.get', self::identicalTo(['USER_ID' => 503, 'MONTH' => 5, 'YEAR' => 2025]))->willReturn($response);
        $result = (new TimeControl($core, new NullLogger()))->getReports(503, 5, 2025);
        self::assertSame([], $result->getReport()->days);
    }

    public function testGetReportSettings(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $response = $this->createStub(Response::class);
        $data = $this->createStub(ResponseData::class);
        $data->method('getResult')->willReturn(['active' => false, 'user_id' => '503', 'user_admin' => false, 'user_head' => false, 'departments' => [], 'minimum_idle_for_report' => '15', 'report_view_type' => 'none']);
        $response->method('getResponseData')->willReturn($data);
        $core->expects(self::once())->method('call')->with('timeman.timecontrol.reports.settings.get', self::identicalTo([]))->willReturn($response);
        $result = (new TimeControl($core, new NullLogger()))->getReportSettings();
        self::assertFalse($result->getSettings()->active);
        self::assertSame(503, $result->getSettings()->user_id);
    }

    public function testGetReportUsers(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $response = $this->createStub(Response::class);
        $data = $this->createStub(ResponseData::class);
        $data->method('getResult')->willReturn([['id' => '503', 'name' => 'A', 'last_activity_date' => '2025-05-29T16:41:00+03:00']]);
        $response->method('getResponseData')->willReturn($data);
        $core->expects(self::once())->method('call')->with('timeman.timecontrol.reports.users.get', self::identicalTo(['DEPARTMENT_ID' => 9]))->willReturn($response);
        $result = (new TimeControl($core, new NullLogger()))->getReportUsers(9);
        self::assertSame(503, $result->getUsers()[0]->id);
        self::assertInstanceOf(\Carbon\CarbonImmutable::class, $result->getUsers()[0]->last_activity_date);
    }

    public function testGetReportUsersEmpty(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $response = $this->createStub(Response::class);
        $data = $this->createStub(ResponseData::class);
        $data->method('getResult')->willReturn([]);
        $response->method('getResponseData')->willReturn($data);
        $core->expects(self::once())->method('call')->with('timeman.timecontrol.reports.users.get', self::identicalTo([]))->willReturn($response);
        $result = (new TimeControl($core, new NullLogger()))->getReportUsers();
        self::assertSame([], $result->getUsers());
    }

    public function testGetSettings(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $response = $this->createStub(Response::class);
        $data = $this->createStub(ResponseData::class);
        $data->method('getResult')->willReturn(['active' => false, 'minimum_idle_for_report' => '15', 'register_idle' => true, 'report_request_users' => []]);
        $response->method('getResponseData')->willReturn($data);
        $core->expects(self::once())->method('call')->with('timeman.timecontrol.settings.get', self::identicalTo([]))->willReturn($response);
        $result = (new TimeControl($core, new NullLogger()))->getSettings();
        self::assertFalse($result->getSettings()->active);
        self::assertSame(15, $result->getSettings()->minimum_idle_for_report);
    }

    public function testSetSettings(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $response = $this->createStub(Response::class);
        $data = $this->createStub(ResponseData::class);
        $data->method('getResult')->willReturn([true]);
        $response->method('getResponseData')->willReturn($data);
        $core->expects(self::once())->method('call')->with('timeman.timecontrol.settings.set', self::identicalTo(['ACTIVE' => 0, 'REGISTER_IDLE' => false, 'REPORT_REQUEST_USERS' => []]))->willReturn($response);
        $result = (new TimeControl($core, new NullLogger()))->setSettings(['ACTIVE' => false, 'REGISTER_IDLE' => false, 'REPORT_REQUEST_USERS' => []]);
        self::assertTrue($result->isSuccess());
    }

    public function testSetSettingsOmittedActive(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $response = $this->createStub(Response::class);
        $data = $this->createStub(ResponseData::class);
        $data->method('getResult')->willReturn([true]);
        $response->method('getResponseData')->willReturn($data);
        $core->expects(self::once())->method('call')->with('timeman.timecontrol.settings.set', self::identicalTo(['MINIMUM_IDLE_FOR_REPORT' => 0]))->willReturn($response);
        $result = (new TimeControl($core, new NullLogger()))->setSettings(['MINIMUM_IDLE_FOR_REPORT' => 0]);
        self::assertTrue($result->isSuccess());
    }

    public function testAddReportPropagatesApiErrors(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $baseException = new \Bitrix24\SDK\Core\Exceptions\BaseException('ACCESS_ERROR');
        $core->expects(self::once())->method('call')->willThrowException($baseException);
        $this->expectExceptionObject($baseException);
        (new TimeControl($core, new NullLogger()))->addReport(27, 'Meeting');
    }

    public function testGetReportsPropagatesApiErrors(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $baseException = new \Bitrix24\SDK\Core\Exceptions\BaseException('ACCESS_ERROR');
        $core->expects(self::once())->method('call')->willThrowException($baseException);
        $this->expectExceptionObject($baseException);
        (new TimeControl($core, new NullLogger()))->getReports(503, 5, 2025);
    }

    public function testGetReportSettingsPropagatesApiErrors(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $baseException = new \Bitrix24\SDK\Core\Exceptions\BaseException('ACCESS_ERROR');
        $core->expects(self::once())->method('call')->willThrowException($baseException);
        $this->expectExceptionObject($baseException);
        (new TimeControl($core, new NullLogger()))->getReportSettings();
    }

    public function testGetReportUsersPropagatesApiErrors(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $baseException = new \Bitrix24\SDK\Core\Exceptions\BaseException('ACCESS_ERROR');
        $core->expects(self::once())->method('call')->willThrowException($baseException);
        $this->expectExceptionObject($baseException);
        (new TimeControl($core, new NullLogger()))->getReportUsers();
    }

    public function testGetSettingsPropagatesApiErrors(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $baseException = new \Bitrix24\SDK\Core\Exceptions\BaseException('ACCESS_ERROR');
        $core->expects(self::once())->method('call')->willThrowException($baseException);
        $this->expectExceptionObject($baseException);
        (new TimeControl($core, new NullLogger()))->getSettings();
    }

    public function testSetSettingsPropagatesApiErrors(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $baseException = new \Bitrix24\SDK\Core\Exceptions\BaseException('ACCESS_ERROR');
        $core->expects(self::once())->method('call')->willThrowException($baseException);
        $this->expectExceptionObject($baseException);
        (new TimeControl($core, new NullLogger()))->setSettings([]);
    }

}
