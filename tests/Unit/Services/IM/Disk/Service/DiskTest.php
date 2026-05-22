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

namespace Bitrix24\SDK\Tests\Unit\Services\IM\Disk\Service;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Core\Response\DTO\Pagination;
use Bitrix24\SDK\Core\Response\DTO\ResponseData;
use Bitrix24\SDK\Core\Response\DTO\Time;
use Bitrix24\SDK\Services\IM\Disk\Result\FileCommitResult;
use Bitrix24\SDK\Services\IM\Disk\Result\FileDeleteResult;
use Bitrix24\SDK\Services\IM\Disk\Result\FileSaveResult;
use Bitrix24\SDK\Services\IM\Disk\Result\FolderIdResult;
use Bitrix24\SDK\Services\IM\Disk\Result\RecordShareResult;
use Bitrix24\SDK\Services\IM\Disk\Service\Disk;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(Disk::class)]
final class DiskTest extends TestCase
{
    public function testGetFolderIdMapsChatIdAndDialogId(): void
    {
        $response = $this->createStub(Response::class);
        $response
            ->method('getResponseData')
            ->willReturn(new ResponseData(['ID' => 5153], Time::initWithZeroValues(), new Pagination()));

        $core = $this->createMock(CoreInterface::class);
        $core
            ->expects($this->once())
            ->method('call')
            ->with(
                'im.disk.folder.get',
                [
                    'CHAT_ID' => 17,
                    'DIALOG_ID' => 'chat17',
                ]
            )
            ->willReturn($response);

        $folderIdResult = (new Disk($core, new NullLogger()))->getFolderId(17, 'chat17');

        $this->assertInstanceOf(FolderIdResult::class, $folderIdResult);
        $this->assertSame(5153, $folderIdResult->getId());
    }

    public function testCommitFileMapsChatAndFilePayload(): void
    {
        $response = $this->createStub(Response::class);

        $core = $this->createMock(CoreInterface::class);
        $core
            ->expects($this->once())
            ->method('call')
            ->with(
                'im.disk.file.commit',
                [
                    'CHAT_ID' => 17,
                    'FILE_ID' => [5249, 5250],
                ]
            )
            ->willReturn($response);

        $fileCommitResult = (new Disk($core, new NullLogger()))->commitFile(
            chatId: 17,
            fileId: [5249, 5250],
        );

        $this->assertInstanceOf(FileCommitResult::class, $fileCommitResult);
    }

    public function testCommitFileMapsDialogUploadFlagsAndMessage(): void
    {
        $response = $this->createStub(Response::class);

        $core = $this->createMock(CoreInterface::class);
        $core
            ->expects($this->once())
            ->method('call')
            ->with(
                'im.disk.file.commit',
                [
                    'DIALOG_ID' => 'chat17',
                    'UPLOAD_ID' => 991,
                    'MESSAGE' => 'Documents by project',
                    'SILENT_MODE' => 'Y',
                    'AS_FILE' => 'N',
                ]
            )
            ->willReturn($response);

        $fileCommitResult = (new Disk($core, new NullLogger()))->commitFile(
            dialogId: 'chat17',
            uploadId: 991,
            message: 'Documents by project',
            silentMode: true,
            asFile: false,
        );

        $this->assertInstanceOf(FileCommitResult::class, $fileCommitResult);
    }

    public function testDeleteFileMapsPayload(): void
    {
        $response = $this->createStub(Response::class);

        $core = $this->createMock(CoreInterface::class);
        $core
            ->expects($this->once())
            ->method('call')
            ->with(
                'im.disk.file.delete',
                [
                    'CHAT_ID' => 17,
                    'FILE_ID' => 5249,
                ]
            )
            ->willReturn($response);

        $fileDeleteResult = (new Disk($core, new NullLogger()))->deleteFile(17, 5249);

        $this->assertInstanceOf(FileDeleteResult::class, $fileDeleteResult);
    }

    public function testSaveFileMapsPayload(): void
    {
        $response = $this->createStub(Response::class);

        $core = $this->createMock(CoreInterface::class);
        $core
            ->expects($this->once())
            ->method('call')
            ->with(
                'im.disk.file.save',
                [
                    'FILE_ID' => 5249,
                ]
            )
            ->willReturn($response);

        $fileSaveResult = (new Disk($core, new NullLogger()))->saveFile(5249);

        $this->assertInstanceOf(FileSaveResult::class, $fileSaveResult);
    }

    public function testShareRecordMapsPayload(): void
    {
        $response = $this->createStub(Response::class);

        $core = $this->createMock(CoreInterface::class);
        $core
            ->expects($this->once())
            ->method('call')
            ->with(
                'im.disk.record.share',
                [
                    'DIALOG_ID' => 'chat17',
                    'DISK_ID' => 5249,
                ]
            )
            ->willReturn($response);

        $recordShareResult = (new Disk($core, new NullLogger()))->shareRecord('chat17', 5249);

        $this->assertInstanceOf(RecordShareResult::class, $recordShareResult);
    }
}
