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

namespace Bitrix24\SDK\Tests\Unit\Services\IM\Disk\Result;

use Bitrix24\SDK\Core\Response\DTO\Pagination;
use Bitrix24\SDK\Core\Response\DTO\ResponseData;
use Bitrix24\SDK\Core\Response\DTO\Time;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Services\IM\Disk\Result\FileCommitResult;
use Bitrix24\SDK\Services\IM\Disk\Result\FileDeleteResult;
use Bitrix24\SDK\Services\IM\Disk\Result\FileSaveResult;
use Bitrix24\SDK\Services\IM\Disk\Result\RecordShareResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(FileCommitResult::class)]
#[CoversClass(FileDeleteResult::class)]
#[CoversClass(FileSaveResult::class)]
#[CoversClass(RecordShareResult::class)]
final class DiskFileResultTest extends TestCase
{
    #[Test]
    public function testFileCommitResultExtractsPayloadParts(): void
    {
        $fileCommitResult = new FileCommitResult($this->response([
            'FILES' => [
                'upload5249' => ['id' => 5249],
            ],
            'DISK_ID' => ['5249'],
            'FILE_MODELS' => [
                'upload5249' => [
                    'id' => 5249,
                    'MESSAGE_ID' => 84779,
                ],
            ],
        ]));

        self::assertSame(['upload5249' => ['id' => 5249]], $fileCommitResult->files());
        self::assertSame([5249], $fileCommitResult->diskIds());
        self::assertSame(['upload5249' => ['id' => 5249, 'MESSAGE_ID' => 84779]], $fileCommitResult->fileModels());
        self::assertSame(84779, $fileCommitResult->messageId());
    }

    #[Test]
    public function testFileSaveResultExtractsFolderAndFileIds(): void
    {
        $fileSaveResult = new FileSaveResult($this->response([
            'folder' => [
                'id' => 4821,
                'name' => 'Saved files',
            ],
            'file' => [
                'id' => 5159,
                'name' => 'image.jpg',
            ],
        ]));

        self::assertSame(['id' => 4821, 'name' => 'Saved files'], $fileSaveResult->folder());
        self::assertSame(['id' => 5159, 'name' => 'image.jpg'], $fileSaveResult->file());
        self::assertSame(4821, $fileSaveResult->folderId());
        self::assertSame(5159, $fileSaveResult->fileId());
    }

    #[Test]
    public function testFileDeleteResultReturnsFalseFromNormalizedScalarFalse(): void
    {
        $fileDeleteResult = new FileDeleteResult($this->response([false]));

        self::assertFalse($fileDeleteResult->isSuccess());
    }

    #[Test]
    public function testRecordShareResultReturnsFalseFromNormalizedScalarFalse(): void
    {
        $recordShareResult = new RecordShareResult($this->response([false]));

        self::assertFalse($recordShareResult->isSuccess());
    }

    /**
     * @param array<string|int, mixed> $payload
     */
    private function response(array $payload): Response
    {
        $response = $this->createStub(Response::class);
        $response
            ->method('getResponseData')
            ->willReturn(new ResponseData($payload, Time::initWithZeroValues(), new Pagination()));

        return $response;
    }
}
