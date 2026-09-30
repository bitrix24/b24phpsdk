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

namespace Bitrix24\SDK\Tests\Unit\Services\Timeman\NetworkRange\Service;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Core\Response\DTO\ResponseData;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Log\NullLogger;
use Bitrix24\SDK\Services\Timeman\NetworkRange\Service\NetworkRange;

#[CoversClass(NetworkRange::class)]
class NetworkRangeTest extends TestCase
{
    public function testCheck(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $response = $this->createStub(Response::class);
        $data = $this->createStub(ResponseData::class);
        $data->method('getResult')->willReturn(['ip' => '10.0.0.1', 'range' => '10.0.0.0/8', 'name' => 'Office']);
        $response->method('getResponseData')->willReturn($data);
        $core->expects(self::once())->method('call')->with('timeman.networkrange.check', self::identicalTo(['IP' => '10.0.0.1']))->willReturn($response);
        $result = (new NetworkRange($core, new NullLogger()))->check('10.0.0.1');
        self::assertSame('Office', $result->getRange()->name);
    }

    public function testCheckUnmatched(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $response = $this->createStub(Response::class);
        $data = $this->createStub(ResponseData::class);
        $data->method('getResult')->willReturn([false]);
        $response->method('getResponseData')->willReturn($data);
        $core->expects(self::once())->method('call')->with('timeman.networkrange.check', self::identicalTo([]))->willReturn($response);
        $result = (new NetworkRange($core, new NullLogger()))->check();
        self::assertNull($result->getRange());
    }

    public function testGet(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $response = $this->createStub(Response::class);
        $data = $this->createStub(ResponseData::class);
        $data->method('getResult')->willReturn([['ip_range' => '10.0.0.0/8', 'name' => 'Office']]);
        $response->method('getResponseData')->willReturn($data);
        $core->expects(self::once())->method('call')->with('timeman.networkrange.get', self::identicalTo([]))->willReturn($response);
        $result = (new NetworkRange($core, new NullLogger()))->get();
        self::assertSame('Office', $result->getRanges()[0]->name);
    }

    public function testGetEmpty(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $response = $this->createStub(Response::class);
        $data = $this->createStub(ResponseData::class);
        $data->method('getResult')->willReturn([]);
        $response->method('getResponseData')->willReturn($data);
        $core->expects(self::once())->method('call')->with('timeman.networkrange.get', self::identicalTo([]))->willReturn($response);
        $result = (new NetworkRange($core, new NullLogger()))->get();
        self::assertSame([], $result->getRanges());
    }

    public function testSet(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $response = $this->createStub(Response::class);
        $data = $this->createStub(ResponseData::class);
        $data->method('getResult')->willReturn(['result' => true]);
        $response->method('getResponseData')->willReturn($data);
        $core->expects(self::once())->method('call')->with('timeman.networkrange.set', self::identicalTo(['RANGES' => [['ip_range' => '10.0.0.0/8', 'name' => 'Office']]]))->willReturn($response);
        $result = (new NetworkRange($core, new NullLogger()))->set([['ip_range' => '10.0.0.0/8', 'name' => 'Office']]);
        self::assertTrue($result->isSuccess());
        self::assertSame([], $result->getErrorRanges());
    }

    public function testSetErrors(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $response = $this->createStub(Response::class);
        $data = $this->createStub(ResponseData::class);
        $data->method('getResult')->willReturn(['result' => false, 'error_ranges' => [['ip_range' => 'invalid', 'name' => 'Bad']]]);
        $response->method('getResponseData')->willReturn($data);
        $core->expects(self::once())->method('call')->with('timeman.networkrange.set', self::identicalTo(['RANGES' => [['ip_range' => 'invalid', 'name' => 'Bad']]]))->willReturn($response);
        $result = (new NetworkRange($core, new NullLogger()))->set([['ip_range' => 'invalid', 'name' => 'Bad']]);
        self::assertFalse($result->isSuccess());
        self::assertSame('invalid', $result->getErrorRanges()[0]->ip_range);
    }

    public function testCheckPropagatesApiErrors(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $baseException = new \Bitrix24\SDK\Core\Exceptions\BaseException('ACCESS_ERROR');
        $core->expects(self::once())->method('call')->willThrowException($baseException);
        $this->expectExceptionObject($baseException);
        (new NetworkRange($core, new NullLogger()))->check('10.0.0.1');
    }

    public function testGetPropagatesApiErrors(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $baseException = new \Bitrix24\SDK\Core\Exceptions\BaseException('ACCESS_ERROR');
        $core->expects(self::once())->method('call')->willThrowException($baseException);
        $this->expectExceptionObject($baseException);
        (new NetworkRange($core, new NullLogger()))->get();
    }

    public function testSetPropagatesApiErrors(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $baseException = new \Bitrix24\SDK\Core\Exceptions\BaseException('ACCESS_ERROR');
        $core->expects(self::once())->method('call')->willThrowException($baseException);
        $this->expectExceptionObject($baseException);
        (new NetworkRange($core, new NullLogger()))->set([]);
    }

}
