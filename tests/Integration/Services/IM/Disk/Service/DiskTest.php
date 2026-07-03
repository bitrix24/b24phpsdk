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

namespace Bitrix24\SDK\Tests\Integration\Services\IM\Disk\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Disk\File\Service\File;
use Bitrix24\SDK\Services\Disk\Folder\Service\Folder;
use Bitrix24\SDK\Services\IM\Disk\Service\Disk;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Disk::class)]
class DiskTest extends TestCase
{
    private Disk $diskService;

    private Folder $folderService;

    private File $fileService;

    #[\Override]
    protected function setUp(): void
    {
        $this->diskService = Factory::getServiceBuilder()->getIMScope()->disk();
        $this->folderService = Factory::getServiceBuilder()->getDiskScope()->folder();
        $this->fileService = Factory::getServiceBuilder()->getDiskScope()->file();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetFolderId(): void
    {
        $chatId = $this->createChat();

        try {
            $folderId = $this->diskService->getFolderId(dialogId: 'chat' . $chatId)->getId();

            $this->assertGreaterThan(0, $folderId);
        } finally {
            $this->leaveChat($chatId);
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testFileCommitSaveAndDelete(): void
    {
        $chatId = $this->createChat();
        $chatFileId = null;
        $savedFileId = null;

        try {
            $dialogId = 'chat' . $chatId;
            $folderId = $this->diskService->getFolderId(dialogId: $dialogId)->getId();
            $sourceFileId = $this->uploadTinyFileToImFolder($folderId);

            $commitResult = $this->diskService->commitFile(dialogId: $dialogId, fileId: $sourceFileId);
            $files = $commitResult->files();
            $fileModels = $commitResult->fileModels();
            $diskIds = $commitResult->diskIds();
            $resultKey = 'disk' . $sourceFileId;

            self::assertNotEmpty($files);
            self::assertNotEmpty($fileModels);
            self::assertNotEmpty($diskIds);
            self::assertGreaterThan(0, $commitResult->messageId());
            self::assertArrayHasKey($resultKey, $files);
            self::assertArrayHasKey($resultKey, $fileModels);

            $chatFileId = $diskIds[0];
            self::assertSame($chatFileId, (int)$files[$resultKey]['id']);
            self::assertSame($chatFileId, (int)$fileModels[$resultKey]['id']);

            $saveResult = $this->diskService->saveFile($chatFileId);
            $savedFileId = $saveResult->fileId();

            self::assertGreaterThan(0, $saveResult->folderId());
            self::assertGreaterThan(0, $savedFileId);

            self::assertTrue($this->diskService->deleteFile($chatId, $chatFileId)->isSuccess());
            $chatFileId = null;
        } finally {
            if ($savedFileId !== null) {
                $this->deleteDiskFile($savedFileId);
            }

            if ($chatFileId !== null) {
                $this->deleteImDiskFile($chatId, $chatFileId);
            }

            $this->leaveChat($chatId);
        }
    }

    public function testShareRecordRequiresRecordCompatibleDiskId(): void
    {
        $userId = (int)$this->diskService->core->call('PROFILE')->getResponseData()->getResult()['ID'];

        $this->expectException(BaseException::class);
        $this->expectExceptionMessage('execute_error - error during record share');

        $this->diskService->shareRecord((string)$userId, 999999999);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    private function createChat(): int
    {
        $userId = (int)$this->diskService->core->call('PROFILE')->getResponseData()->getResult()['ID'];

        return (int)$this->diskService->core->call(
            'im.chat.add',
            [
                'USERS' => [$userId],
                'TYPE' => 'CHAT',
                'TITLE' => sprintf('IM Disk Test %s', time()),
            ]
        )->getResponseData()->getResult()[0];
    }

    private function leaveChat(int $chatId): void
    {
        try {
            $this->diskService->core->call('im.chat.leave', ['CHAT_ID' => $chatId]);
        } catch (BaseException) {
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    private function uploadTinyFileToImFolder(int $folderId): int
    {
        return $this->folderService->uploadFile(
            $folderId,
            ['NAME' => sprintf('im_disk_%s.txt', time())],
            base64_encode('IM Disk integration test ' . time()),
            true,
        )->getId();
    }

    private function deleteDiskFile(int $fileId): void
    {
        try {
            $this->fileService->delete($fileId);
        } catch (BaseException) {
        }
    }

    private function deleteImDiskFile(int $chatId, int $fileId): void
    {
        try {
            $this->diskService->deleteFile($chatId, $fileId);
        } catch (BaseException) {
        }
    }
}
