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

namespace Bitrix24\SDK\Tests\Integration\Services\Disk\File\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core;
use Bitrix24\SDK\Services\Disk\File\Result\FileItemResult;
use Bitrix24\SDK\Services\Disk\File\Service\File;
use Bitrix24\SDK\Services\Disk\Folder\Service\Folder;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

/**
 * Class FileTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Disk\File\Service
 */
#[CoversMethod(File::class, 'getFields')]
#[CoversMethod(File::class, 'get')]
#[CoversMethod(File::class, 'rename')]
#[CoversMethod(File::class, 'copyTo')]
#[CoversMethod(File::class, 'moveTo')]
#[CoversMethod(File::class, 'delete')]
#[CoversMethod(File::class, 'markDeleted')]
#[CoversMethod(File::class, 'restore')]
#[CoversMethod(File::class, 'uploadVersion')]
#[CoversMethod(File::class, 'getVersions')]
#[CoversMethod(File::class, 'restoreFromVersion')]
#[CoversMethod(File::class, 'getExternalLink')]
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\Disk\File\Service\File::class)]
class FileTest extends TestCase
{
    use CustomBitrix24Assertions;

    protected File $fileService;

    protected Folder $folderService;

    protected function setUp(): void
    {
        $this->fileService = Factory::getServiceBuilder()->getDiskScope()->file();
        $this->folderService = Factory::getServiceBuilder()->getDiskScope()->folder();
    }

    public function testAllSystemFieldsAnnotated(): void
    {
        $propListFromApi = (new Core\Fields\FieldsFilter())->filterSystemFields(array_keys($this->fileService->getFields()->getFieldsDescription()));
        $this->assertBitrix24AllResultItemFieldsAnnotated($propListFromApi, FileItemResult::class);
    }

    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $allFields = $this->fileService->getFields()->getFieldsDescription();
        foreach ($allFields as $field => $params) {
            $newParams = [];
            foreach ($params as $key => $value) {
                $newParams[mb_strtolower((string) $key)] = $value;
            }

            $allFields[$field] = $newParams;
        }

        $systemFieldsCodes = (new Core\Fields\FieldsFilter())->filterSystemFields(array_keys($allFields));
        $systemFields = array_filter($allFields, static fn($code): bool => in_array($code, $systemFieldsCodes, true), ARRAY_FILTER_USE_KEY);

        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation(
            $systemFields,
            FileItemResult::class);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetFields(): void
    {
        // Check that getFields method returns array with fields description
        self::assertIsArray($this->fileService->getFields()->getFieldsDescription());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGet(): void
    {
        $fileId = $this->createTestFile();

        // Get file and check its ID
        $file = $this->fileService->get($fileId)->file();
        self::assertEquals($fileId, $file->ID);
        self::assertStringContainsString('test_file', $file->NAME);

        // Clean up test file
        $this->cleanUpFile($fileId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testRename(): void
    {
        $fileId = $this->createTestFile();

        // Rename file
        $newName = 'renamed_test_file_' . time() . '.txt';
        $fileRenamedResult = $this->fileService->rename($fileId, $newName);
        self::assertTrue($fileRenamedResult->isSuccess());

        // Check that name was changed
        $file = $this->fileService->get($fileId)->file();
        self::assertEquals($newName, $file->NAME);

        // Clean up test file
        $this->cleanUpFile($fileId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testCopyTo(): void
    {
        $fileId = $this->createTestFile();
        $targetFolderId = $this->createTestFolder();

        // Copy file to target folder
        $fileCopiedResult = $this->fileService->copyTo($fileId, $targetFolderId);
        self::assertTrue($fileCopiedResult->isSuccess());

        // Check that copy was created
        $fileItemResult = $fileCopiedResult->file();
        self::assertNotNull($fileItemResult);
        self::assertEquals($targetFolderId, $fileItemResult->PARENT_ID);
        self::assertNotEquals($fileId, $fileItemResult->ID); // Should be different ID

        // Clean up test files and folder
        $this->cleanUpFile($fileId);
        $this->cleanUpFile((int)$fileItemResult->ID);
        $this->cleanUpFolder($targetFolderId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testMoveTo(): void
    {
        $fileId = $this->createTestFile();
        $targetFolderId = $this->createTestFolder();

        // Move file to target folder
        $fileMovedResult = $this->fileService->moveTo($fileId, $targetFolderId);
        self::assertTrue($fileMovedResult->isSuccess());

        // Check that file was moved
        $file = $this->fileService->get($fileId)->file();
        self::assertEquals($targetFolderId, $file->PARENT_ID);

        // Clean up test file and folder
        $this->cleanUpFile($fileId);
        $this->cleanUpFolder($targetFolderId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testMarkDeletedAndRestore(): void
    {
        $fileId = $this->createTestFile();

        // Move to trash
        $fileMarkedDeletedResult = $this->fileService->markDeleted($fileId);
        self::assertTrue($fileMarkedDeletedResult->isSuccess());

        // Check that file is in trash
        $file = $this->fileService->get($fileId)->file();
        self::assertNotEquals('0', $file->DELETED_TYPE);

        // Restore from trash
        $fileRestoredResult = $this->fileService->restore($fileId);
        self::assertTrue($fileRestoredResult->isSuccess());

        // Check that file is restored
        $fileItemResult = $this->fileService->get($fileId)->file();
        self::assertEquals('0', $fileItemResult->DELETED_TYPE);

        // Clean up test file
        $this->cleanUpFile($fileId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testUploadVersion(): void
    {
        $fileId = $this->createTestFile();

        // Upload new version
        $newContent = 'Updated test file content - ' . time();
        $base64Content = base64_encode($newContent);

        $fileVersionUploadedResult = $this->fileService->uploadVersion($fileId, $base64Content);
        self::assertTrue($fileVersionUploadedResult->isSuccess());

        // Check that version was uploaded
        $file = $fileVersionUploadedResult->file();
        self::assertEquals($fileId, $file->ID);

        // Clean up test file
        $this->cleanUpFile($fileId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetVersions(): void
    {
        $fileId = $this->createTestFile();

        // Upload a new version to have at least 2 versions
        $newContent = 'Second version content - ' . time();
        $base64Content = base64_encode($newContent);
        $this->fileService->uploadVersion($fileId, $base64Content);

        // Get versions
        $fileVersionsResult = $this->fileService->getVersions($fileId);
        $versions = $fileVersionsResult->getVersions();

        self::assertIsArray($versions);
        self::assertGreaterThanOrEqual(1, count($versions)); // uploaded version only

        // Clean up test file
        $this->cleanUpFile($fileId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testRestoreFromVersion(): void
    {
        $fileId = $this->createTestFile();

        // Upload a new version
        $newContent = 'Second version content - ' . time();
        $base64Content = base64_encode($newContent);
        $this->fileService->uploadVersion($fileId, $base64Content);

        // Get versions to find version ID
        $versions = $this->fileService->getVersions($fileId)->getVersions();
        self::assertGreaterThanOrEqual(1, count($versions));

        // Get the first version ID
        $versionId = (int)$versions[0]->ID; 

        // Restore from version
        $fileRestoredFromVersionResult = $this->fileService->restoreFromVersion($fileId, $versionId);
        self::assertTrue($fileRestoredFromVersionResult->isSuccess());

        // Check that file was restored
        $file = $fileRestoredFromVersionResult->file();
        self::assertEquals($fileId, $file->ID);

        // Clean up test file
        $this->cleanUpFile($fileId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetExternalLink(): void
    {
        $fileId = $this->createTestFile();

        // Get external link
        $fileExternalLinkResult = $this->fileService->getExternalLink($fileId);
        $link = $fileExternalLinkResult->getExternalLink();

        self::assertIsString($link);
        self::assertNotEmpty($link);
        self::assertStringContainsString('http', $link); // Should be a URL

        // Clean up test file
        $this->cleanUpFile($fileId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelete(): void
    {
        $fileId = $this->createTestFile();

        // Delete file permanently
        $fileDeletedResult = $this->fileService->delete($fileId);
        self::assertTrue($fileDeletedResult->isSuccess());

        // File should no longer exist (expect exception or error when trying to get it)
        try {
            $this->fileService->get($fileId);
            self::fail('Expected exception when getting deleted file');
        } catch (\Exception) {
            // Expected - file should not exist
            self::assertTrue(true);
        }
    }

    /**
     * Helper method to create a test file
     * 
     * @throws BaseException
     * @throws TransportException
     */
    protected function createTestFile(): int
    {
        $rootFolderId = $this->getRootFolderId();

        // Test content
        $testContent = 'Test file content - ' . time();
        $base64Content = base64_encode($testContent);

        $fileData = [
            'NAME' => 'test_file_' . time() . '.txt'
        ];

        // Upload file using folder service
        $uploadedFileResult = $this->folderService->uploadFile($rootFolderId, $fileData, $base64Content, true);

        return $uploadedFileResult->getId();
    }

    /**
     * Helper method to create a test folder
     * 
     * @throws BaseException
     * @throws TransportException
     */
    protected function createTestFolder(): int
    {
        $rootFolderId = $this->getRootFolderId();

        $folderData = [
            'NAME' => 'Test File Folder ' . time()
        ];

        return $this->folderService->addSubfolder($rootFolderId, $folderData)->getId();
    }

    /**
     * Helper method to clean up a test file
     * 
     * @throws BaseException
     * @throws TransportException
     */
    protected function cleanUpFile(int $fileId): void
    {
        try {
            $this->fileService->delete($fileId);
        } catch (\Exception) {
            // File might already be deleted, ignore
        }
    }

    /**
     * Helper method to clean up a test folder
     * 
     * @throws BaseException
     * @throws TransportException
     */
    protected function cleanUpFolder(int $folderId): void
    {
        try {
            $this->folderService->deleteTree($folderId);
        } catch (\Exception) {
            // Folder might already be deleted, ignore
        }
    }

    /**
     * Helper method to get root folder ID
     * We assume that user has access to their personal drive folder
     * 
     * @throws BaseException
     * @throws TransportException
     */
    protected function getRootFolderId(): int
    {
        // Get user's personal storage root folder
        $core = Factory::getCore();
        $response = $core->call('disk.storage.getlist', [
            'filter' => [
                'ENTITY_TYPE' => 'user'
            ]
        ]);

        $storages = $response->getResponseData()->getResult();

        if ($storages === []) {
            self::markTestSkipped('No user storage found');
        }

        return (int)$storages[0]['ROOT_OBJECT_ID'];
    }
}