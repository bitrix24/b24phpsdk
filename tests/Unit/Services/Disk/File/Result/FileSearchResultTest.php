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

namespace Bitrix24\SDK\Tests\Unit\Services\Disk\File\Result;

use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Core\Response\DTO\Pagination;
use Bitrix24\SDK\Core\Response\DTO\ResponseData;
use Bitrix24\SDK\Core\Response\DTO\Time;
use Bitrix24\SDK\Services\Disk\File\Result\FileSearchItemResult;
use Bitrix24\SDK\Services\Disk\File\Result\FileSearchResult;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FileSearchResult::class)]
#[CoversClass(FileSearchItemResult::class)]
final class FileSearchResultTest extends TestCase
{
    public function testMixedSearchItemsCastValuesWithoutInventingMissingFields(): void
    {
        $response = $this->createStub(Response::class);
        $response->method('getResponseData')->willReturn(new ResponseData([
            ['ID' => '42', 'TYPE' => 'file', 'SIZE' => '12', 'FILE_ID' => '9', 'GLOBAL_CONTENT_VERSION' => '2',
                'CREATE_TIME' => '2026-09-30T10:00:00+03:00', 'DELETE_TIME' => null, 'CODE' => null,
                'DOWNLOAD_URL' => 'https://example.com/download', 'DETAIL_URL' => null],
            ['ID' => '43', 'TYPE' => 'folder', 'REAL_OBJECT_ID' => '44', 'DELETED_TYPE' => '0'],
        ], Time::initWithZeroValues(), new Pagination()));
        [$file, $folder] = (new FileSearchResult($response))->items();
        self::assertSame(42, $file->ID);
        self::assertSame(12, $file->SIZE);
        self::assertSame(9, $file->FILE_ID);
        self::assertSame(2, $file->GLOBAL_CONTENT_VERSION);
        self::assertInstanceOf(CarbonImmutable::class, $file->CREATE_TIME);
        self::assertSame('2026-09-30T10:00:00+03:00', $file->CREATE_TIME->toIso8601String());
        self::assertNull($file->DELETE_TIME);
        self::assertNull($file->CODE);
        self::assertNull($file->DETAIL_URL);
        self::assertSame('https://example.com/download', $file->DOWNLOAD_URL);
        self::assertNull($file->REAL_OBJECT_ID);
        self::assertSame(44, $folder->REAL_OBJECT_ID);
        self::assertSame(0, $folder->DELETED_TYPE);
        self::assertNull($folder->FILE_ID);
        self::assertNull($folder->DOWNLOAD_URL);
        self::assertNull($folder->SIZE);
    }
}
