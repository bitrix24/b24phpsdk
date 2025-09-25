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

namespace Bitrix24\SDK\Tests\Integration\Services\Disk\Folder\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core;
use Bitrix24\SDK\Services\Disk\Folder\Result\FolderItemResult;
use Bitrix24\SDK\Services\Disk\Folder\Service\Folder;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

/**
 * Class FolderTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Disk\Folder\Service
 */
#[CoversMethod(Folder::class, 'getFields')]
#[CoversMethod(Folder::class, 'get')]
#[CoversMethod(Folder::class, 'getChildren')]
#[CoversMethod(Folder::class, 'addSubfolder')]
#[CoversMethod(Folder::class, 'copyTo')]
#[CoversMethod(Folder::class, 'moveTo')]
#[CoversMethod(Folder::class, 'rename')]
#[CoversMethod(Folder::class, 'markDeleted')]
#[CoversMethod(Folder::class, 'restore')]
#[CoversMethod(Folder::class, 'deleteTree')]
#[CoversMethod(Folder::class, 'getExternalLink')]
#[CoversMethod(Folder::class, 'uploadFile')]
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\Disk\Folder\Service\Folder::class)]
class FolderTest extends TestCase
{
    use CustomBitrix24Assertions;
    
    protected Folder $folderService;
    
    protected function setUp(): void
    {
        $this->folderService = Fabric::getServiceBuilder()->getDiskScope()->folder();
    }

    public function testAllSystemFieldsAnnotated(): void
    {
        $propListFromApi = (new Core\Fields\FieldsFilter())->filterSystemFields(array_keys($this->folderService->getFields()->getFieldsDescription()));
        $this->assertBitrix24AllResultItemFieldsAnnotated($propListFromApi, FolderItemResult::class);
    }
    
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $allFields = $this->folderService->getFields()->getFieldsDescription();
        foreach ($allFields as $field => $params) {
            $newParams = [];
            foreach ($params as $key => $value) {
                $newParams[mb_strtolower($key)] = $value;
            }
            $allFields[$field] = $newParams;
        }
        $systemFieldsCodes = (new Core\Fields\FieldsFilter())->filterSystemFields(array_keys($allFields));
        $systemFields = array_filter($allFields, static fn($code): bool => in_array($code, $systemFieldsCodes, true), ARRAY_FILTER_USE_KEY);

        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation(
            $systemFields,
            FolderItemResult::class);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetFields(): void
    {
        // Check that getFields method returns array with fields description
        self::assertIsArray($this->folderService->getFields()->getFieldsDescription());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAddSubfolder(): void
    {
        $rootFolderId = $this->getRootFolderId();
        
        $folderData = [
            'NAME' => 'Test Subfolder ' . time()
        ];
        
        $folderId = $this->folderService->addSubfolder($rootFolderId, $folderData)->getId();
        self::assertGreaterThan(0, $folderId);
        
        // Clean up test folder
        $this->folderService->deleteTree($folderId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGet(): void
    {
        $rootFolderId = $this->getRootFolderId();
        
        $folderData = [
            'NAME' => 'Test Get Folder ' . time()
        ];
        
        $folderId = $this->folderService->addSubfolder($rootFolderId, $folderData)->getId();
        
        // Get folder and check its ID
        $folder = $this->folderService->get($folderId)->folder();
        self::assertEquals($folderId, $folder->ID);
        self::assertEquals($folderData['NAME'], $folder->NAME);
        
        // Clean up test folder
        $this->folderService->deleteTree($folderId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetChildren(): void
    {
        $rootFolderId = $this->getRootFolderId();
        
        // Create test subfolder
        $folderData = [
            'NAME' => 'Test Parent Folder ' . time()
        ];
        
        $parentFolderId = $this->folderService->addSubfolder($rootFolderId, $folderData)->getId();
        
        // Create subfolder in parent folder
        $childFolderData = [
            'NAME' => 'Test Child Folder ' . time()
        ];
        
        $childFolderId = $this->folderService->addSubfolder($parentFolderId, $childFolderData)->getId();
        
        // Get parent folder contents
        $children = $this->folderService->getChildren($parentFolderId)->getChildren();
        
        // Check that child folder is present
        $childIds = array_map(fn($child) => $child->ID, $children);
        
        self::assertTrue(in_array($childFolderId, $childIds));
        
        // Clean up test folders
        $this->folderService->deleteTree($parentFolderId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testCopyTo(): void
    {
        $rootFolderId = $this->getRootFolderId();
        
        // Create source folder
        $sourceFolderData = [
            'NAME' => 'Test Source Folder ' . time()
        ];
        
        $sourceFolderId = $this->folderService->addSubfolder($rootFolderId, $sourceFolderData)->getId();
        
        // Create target folder
        $targetFolderData = [
            'NAME' => 'Test Target Folder ' . time()
        ];
        
        $targetFolderId = $this->folderService->addSubfolder($rootFolderId, $targetFolderData)->getId();
        
        // Copy folder
        $result = $this->folderService->copyTo($sourceFolderId, $targetFolderId);
        self::assertTrue($result->isSuccess());
        
        // Check that copy was created
        $copiedFolder = $result->folder();
        self::assertNotNull($copiedFolder);
        self::assertEquals($sourceFolderData['NAME'], $copiedFolder->NAME);
        self::assertEquals($targetFolderId, $copiedFolder->PARENT_ID);
        
        // Clean up test folders
        $this->folderService->deleteTree($sourceFolderId);
        $this->folderService->deleteTree($targetFolderId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testMoveTo(): void
    {
        $rootFolderId = $this->getRootFolderId();
        
        // Create source folder
        $sourceFolderData = [
            'NAME' => 'Test Move Folder ' . time()
        ];
        
        $sourceFolderId = $this->folderService->addSubfolder($rootFolderId, $sourceFolderData)->getId();
        
        // Create target folder
        $targetFolderData = [
            'NAME' => 'Test Target Move Folder ' . time()
        ];
        
        $targetFolderId = $this->folderService->addSubfolder($rootFolderId, $targetFolderData)->getId();
        
        // Move folder
        $result = $this->folderService->moveTo($sourceFolderId, $targetFolderId);
        self::assertTrue($result->isSuccess());
        
        // Check that folder was moved
        $movedFolder = $this->folderService->get($sourceFolderId)->folder();
        self::assertEquals($targetFolderId, $movedFolder->PARENT_ID);
        
        // Clean up test folders
        $this->folderService->deleteTree($targetFolderId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testRename(): void
    {
        $rootFolderId = $this->getRootFolderId();
        
        $folderData = [
            'NAME' => 'Test Rename Folder ' . time()
        ];
        
        $folderId = $this->folderService->addSubfolder($rootFolderId, $folderData)->getId();
        
        // Rename folder
        $newName = 'Renamed Folder ' . time();
        $result = $this->folderService->rename($folderId, $newName);
        self::assertTrue($result->isSuccess());
        
        // Check that name was changed
        $renamedFolder = $this->folderService->get($folderId)->folder();
        self::assertEquals($newName, $renamedFolder->NAME);
        
        // Clean up test folder
        $this->folderService->deleteTree($folderId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testMarkDeletedAndRestore(): void
    {
        $rootFolderId = $this->getRootFolderId();
        
        $folderData = [
            'NAME' => 'Test Delete Restore Folder ' . time()
        ];
        
        $folderId = $this->folderService->addSubfolder($rootFolderId, $folderData)->getId();
        
        // Move to trash
        $deleteResult = $this->folderService->markDeleted($folderId);
        self::assertTrue($deleteResult->isSuccess());
        
        // Check that folder is in trash
        $deletedFolder = $this->folderService->get($folderId)->folder();
        self::assertNotEquals('0', $deletedFolder->DELETED_TYPE);
        
        // Restore from trash
        $restoreResult = $this->folderService->restore($folderId);
        self::assertTrue($restoreResult->isSuccess());
        
        // Check that folder is restored
        $restoredFolder = $this->folderService->get($folderId)->folder();
        self::assertEquals('0', $restoredFolder->DELETED_TYPE);
        
        // Clean up test folder
        $this->folderService->deleteTree($folderId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDeleteTree(): void
    {
        $rootFolderId = $this->getRootFolderId();
        
        $folderData = [
            'NAME' => 'Test Delete Tree Folder ' . time()
        ];
        
        $folderId = $this->folderService->addSubfolder($rootFolderId, $folderData)->getId();
        
        // Check successful deletion
        self::assertTrue($this->folderService->deleteTree($folderId)->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetExternalLink(): void
    {
        $rootFolderId = $this->getRootFolderId();
        
        $folderData = [
            'NAME' => 'Test External Link Folder ' . time()
        ];
        
        $folderId = $this->folderService->addSubfolder($rootFolderId, $folderData)->getId();
        
        // Get external link
        $link = $this->folderService->getExternalLink($folderId)->getLink();
        self::assertIsString($link);
        self::assertNotEmpty($link);
        
        // Clean up test folder
        $this->folderService->deleteTree($folderId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testUploadFile(): void
    {
        $rootFolderId = $this->getRootFolderId();
        
        // Create test folder for file upload
        $folderData = [
            'NAME' => 'Test Upload Folder ' . time()
        ];
        
        $folderId = $this->folderService->addSubfolder($rootFolderId, $folderData)->getId();
        
        // Test content
        $testContent = $this->getFileContent();
        
        // Encode content to base64
        $base64Content = base64_encode($testContent);
        
        $fileData = [
            'NAME' => 'test_text_file.txt'
        ];
        
        // Upload file
        $uploadResult = $this->folderService->uploadFile($folderId, $fileData, $base64Content, true);
        $fileId = $uploadResult->getId();
        self::assertGreaterThan(0, $fileId);
        
        // Check uploaded file data
        $fileInfo = $uploadResult->getFile();
        self::assertEquals($fileData['NAME'], $fileInfo['NAME']);
        self::assertEquals($folderId, $fileInfo['PARENT_ID']);
        
        // Clean up test folder (including file)
        $this->folderService->deleteTree($folderId);
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
        $core = Fabric::getCore();
        $storageResult = $core->call('disk.storage.getlist', [
            'filter' => [
                'ENTITY_TYPE' => 'user'
            ]
        ]);
        
        $storages = $storageResult->getResponseData()->getResult();
        
        if (empty($storages)) {
            self::markTestSkipped('No user storage found');
        }
        
        return (int)$storages[0]['ROOT_OBJECT_ID'];
    }
    
    /**
     * Helper method to get file content for testUploadFile
     */
    protected function getFileContent()
    {
        return 'Test file content';
    }
}