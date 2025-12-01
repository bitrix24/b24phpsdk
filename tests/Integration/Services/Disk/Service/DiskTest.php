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

namespace Bitrix24\SDK\Tests\Integration\Services\Disk\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Disk\Service\Disk;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

/**
 * Class DiskTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Disk\Service
 */
#[CoversMethod(Disk::class, 'getVersion')]
#[CoversMethod(Disk::class, 'getAttachedObject')]
#[CoversMethod(Disk::class, 'getRightsTasks')]
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\Disk\Service\Disk::class)]
class DiskTest extends TestCase
{
    protected Disk $diskService;
    
    protected function setUp(): void
    {
        $this->diskService = Factory::getServiceBuilder(true)->getDiskScope()->disk();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetRightsTasks(): void
    {
        // Test getting access rights tasks
        $rightsResult = $this->diskService->getRightsTasks();
        $rights = $rightsResult->getRights();
        
        self::assertNotEmpty($rights);
        
        // Check that we have at least basic access rights
        $rightNames = array_map(fn($right) => $right->NAME, $rights);
        
        // Verify that standard access rights exist
        self::assertContains('disk_access_read', $rightNames);
        self::assertContains('disk_access_edit', $rightNames);
        self::assertContains('disk_access_full', $rightNames);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetVersion(): void
    {
        // First we need to create a file and get its version
        $fileId = $this->createTestFile();
        
        try {
            // Get file versions to find a version ID using SDK service
            $fileService = Factory::getServiceBuilder(true)->getDiskScope()->file();
            $versionsResult = $fileService->getVersions($fileId);
            $versions = $versionsResult->getVersions();
            
            $versionId = (int)$versions[0]->ID;
            
            // Test getting version information
            $versionResult = $this->diskService->getVersion($versionId);
            
            self::assertEquals($versionId, $versionResult->ID);
        } finally {
            // Clean up test file
            $this->cleanUpTestFile($fileId);
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetAttachedObject(): void
    {
        $fileId = null;
        $taskId = null;
        
        try {
            // Step 1: Create test file
            $fileId = $this->createTestFile();
            
            // Step 2: Create task with attached file
            $taskService = Factory::getServiceBuilder(true)->getTaskScope()->task();
            $taskResult = $taskService->add([
                'TITLE' => 'Test Task with Attached File ' . time(),
                'RESPONSIBLE_ID' => 1, // Assuming user ID 1 exists
                'UF_TASK_WEBDAV_FILES' => ['n' . $fileId] // Add 'n' prefix as per documentation
            ]);
            
            $taskId = $taskResult->getId();
            
            // Step 3: Get task with file attachments to find attached object ID
            $task = $taskService->get($taskId, ['*', 'UF_TASK_WEBDAV_FILES']);
            $taskData = $task->task();
            
            $attachedObjectId = (int)$taskData->ufTaskWebdavFiles[0];
            
            // Step 4: Test getAttachedObject method
            $attachedObjectResult = $this->diskService->getAttachedObject($attachedObjectId);
            
            // Verify attached object properties
            self::assertEquals($attachedObjectId, $attachedObjectResult->ID);
            self::assertEquals($fileId, $attachedObjectResult->OBJECT_ID);
            
            // Verify it's attached to the correct task
            self::assertEquals($taskId, $attachedObjectResult->ENTITY_ID);
            
        } finally {
            // Clean up: delete task first, then file
            if ($taskId !== null) {
                try {
                    $taskService = Factory::getServiceBuilder(true)->getTaskScope()->task();
                    $taskService->delete($taskId);
                } catch (BaseException) {
                    // Ignore cleanup errors
                }
            }
            
            if ($fileId !== null) {
                $this->cleanUpTestFile($fileId);
            }
        }
    }

    /**
     * Helper method to create a test file
     * Returns file ID or null if creation failed
     * 
     * @throws BaseException
     * @throws TransportException
     */
    protected function createTestFile(): ?int
    {
        try {
            $rootFolderId = $this->getRootFolderId();
            
            // Use SDK folder service to upload file
            $folderService = Factory::getServiceBuilder(true)->getDiskScope()->folder();
            $uploadResult = $folderService->uploadFile($rootFolderId, [
                'NAME' => 'test_file_' . time() . '.txt'
            ], 'Test file content for version testing');
            
            return $uploadResult->getId();
            
        } catch (BaseException) {
            return null;
        }
    }

    /**
     * Helper method to clean up test file
     * 
     * @throws BaseException
     * @throws TransportException
     */
    protected function cleanUpTestFile(int $fileId): void
    {
        try {
            // Use SDK file service to delete file
            $fileService = Factory::getServiceBuilder(true)->getDiskScope()->file();
            $fileService->delete($fileId);
        } catch (BaseException) {
            // Ignore cleanup errors
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
        // Use SDK storage service to get user's personal storage
        $storageService = Factory::getServiceBuilder(true)->getDiskScope()->storage();
        $storagesResult = $storageService->list(['ENTITY_TYPE' => 'user']);
        $storages = $storagesResult->storages();
        
        if ($storages === []) {
            self::markTestSkipped('No user storage found');
        }
        
        return (int)$storages[0]->ROOT_OBJECT_ID;
    }
}