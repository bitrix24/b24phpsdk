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

namespace Bitrix24\SDK\Tests\Unit\Services\Disk\File\Service;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Core\Response\DTO\Pagination;
use Bitrix24\SDK\Core\Response\DTO\ResponseData;
use Bitrix24\SDK\Core\Response\DTO\Time;
use Bitrix24\SDK\Services\Disk\File\Service\File;
use Bitrix24\SDK\Services\Disk\File\Result\FileSearchItemResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(File::class)]
final class FileTest extends TestCase
{
    public function testSearchDefaultsAndEmptyResult(): void
    {
        $response = $this->createStub(Response::class);
        $response->method('getResponseData')->willReturn(new ResponseData([], Time::initWithZeroValues(), new Pagination()));
        $core = $this->createMock(CoreInterface::class);
        $core->expects($this->once())->method('call')
            ->with('disk.file.search', ['QUERY' => 'report', 'TYPE' => 'file', 'start' => 0])
            ->willReturn($response);

        $result = (new File($core, new NullLogger()))->search('report');
        self::assertSame([], $result->items());
        self::assertNull($result->getCoreResponse()->getResponseData()->getPagination()->getNextItem());
    }

    public function testSearchMapsScopeAndLowercaseOffsetAndPreservesMixedItems(): void
    {
        $response = $this->createStub(Response::class);
        $response->method('getResponseData')->willReturn(new ResponseData([
            ['ID' => '42', 'TYPE' => 'file', 'NAME' => 'report.txt'],
            ['ID' => '43', 'TYPE' => 'folder', 'NAME' => 'reports'],
        ], Time::initWithZeroValues(), new Pagination(100)));
        $core = $this->createMock(CoreInterface::class);
        $core->expects($this->once())->method('call')->with('disk.file.search', [
            'QUERY' => 'report', 'TYPE' => 'all', 'FILTER' => ['STORAGE_ID' => 7, 'FOLDER_ID' => 8], 'start' => 50,
        ])->willReturn($response);

        $result = (new File($core, new NullLogger()))->search('report', 'all', ['STORAGE_ID' => 7, 'FOLDER_ID' => 8], 50);
        self::assertCount(2, $result->items());
        self::assertInstanceOf(FileSearchItemResult::class, $result->items()[0]);
        self::assertSame('file', $result->items()[0]->TYPE);
        self::assertSame('folder', $result->items()[1]->TYPE);
        self::assertSame(100, $result->getCoreResponse()->getResponseData()->getPagination()->getNextItem());
    }

    public function testSearchPropagatesApiErrors(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $core->expects($this->once())->method('call')->willThrowException(new BaseException('INVALID_QUERY'));
        $this->expectException(BaseException::class);
        $this->expectExceptionMessage('INVALID_QUERY');
        (new File($core, new NullLogger()))->search('x');
    }
}
