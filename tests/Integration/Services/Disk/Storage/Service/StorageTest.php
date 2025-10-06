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

namespace Bitrix24\SDK\Tests\Integration\Services\Disk\Storage\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core;
use Bitrix24\SDK\Services\Disk\Storage\Result\StorageItemResult;
use Bitrix24\SDK\Services\Disk\Storage\Service\Storage;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

/**
 * Class StorageTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Disk\Storage\Service
 */
#[CoversMethod(Storage::class, 'fields')]
#[CoversMethod(Storage::class, 'get')]
#[CoversMethod(Storage::class, 'rename')]
#[CoversMethod(Storage::class, 'list')]
#[CoversMethod(Storage::class, 'getTypes')]
#[CoversMethod(Storage::class, 'addFolder')]
#[CoversMethod(Storage::class, 'getChildren')]
#[CoversMethod(Storage::class, 'uploadFile')]
#[CoversMethod(Storage::class, 'getForApp')]
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\Disk\Storage\Service\Storage::class)]
class StorageTest extends TestCase
{
    use CustomBitrix24Assertions;
    
    protected Storage $storageService;
    
    protected function setUp(): void
    {
        $this->storageService = Fabric::getServiceBuilder(true)->getDiskScope()->storage();
    }

    public function testAllSystemFieldsAnnotated(): void
    {
        $propListFromApi = (new Core\Fields\FieldsFilter())->filterSystemFields(array_keys($this->storageService->fields()->getFieldsDescription()));
        $this->assertBitrix24AllResultItemFieldsAnnotated($propListFromApi, StorageItemResult::class);
    }
    
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $allFields = $this->storageService->fields()->getFieldsDescription();
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
            StorageItemResult::class);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testFields(): void
    {
        // Check that fields method returns array with fields description
        $fields = $this->storageService->fields()->getFieldsDescription();
        self::assertIsArray($fields);
        self::assertArrayHasKey('ID', $fields);
        self::assertArrayHasKey('NAME', $fields);
        self::assertArrayHasKey('ENTITY_TYPE', $fields);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetTypes(): void
    {
        $types = $this->storageService->getTypes()->types();
        self::assertIsArray($types);
        self::assertNotEmpty($types);
        
        // Check that known storage types are present
        $expectedTypes = ['user', 'common', 'group'];
        foreach ($expectedTypes as $expectedType) {
            self::assertContains($expectedType, $types, sprintf("Storage type '%s' should be in the list", $expectedType));
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testList(): void
    {
        $storages = $this->storageService->list()->storages();
        self::assertIsArray($storages);
        self::assertNotEmpty($storages, 'At least one storage should be available');
        
        foreach ($storages as $storage) {
            self::assertInstanceOf(StorageItemResult::class, $storage);
            self::assertIsNumeric($storage->ID);
            self::assertIsString($storage->NAME);
            self::assertIsString($storage->ENTITY_TYPE);
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testListWithFilter(): void
    {
        $storages = $this->storageService->list(['ENTITY_TYPE' => 'user'])->storages();
        self::assertIsArray($storages);
        
        foreach ($storages as $storage) {
            self::assertEquals('user', $storage->ENTITY_TYPE);
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGet(): void
    {
        $storages = $this->storageService->list()->storages();
        self::assertNotEmpty($storages, 'At least one storage should be available for testing get method');
        
        $firstStorage = $storages[0];
        $storageId = (int)$firstStorage->ID;
        
        $storage = $this->storageService->get($storageId)->storage();
        self::assertInstanceOf(StorageItemResult::class, $storage);
        self::assertEquals($storageId, (int)$storage->ID);
        self::assertEquals($firstStorage->NAME, $storage->NAME);
        self::assertEquals($firstStorage->ENTITY_TYPE, $storage->ENTITY_TYPE);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetForApp(): void
    {
        $storageItemResult = $this->storageService->getForApp()->storage();
        self::assertInstanceOf(StorageItemResult::class, $storageItemResult);
        self::assertIsNumeric($storageItemResult->ID);
        self::assertIsString($storageItemResult->NAME);
        self::assertEquals('restapp', $storageItemResult->ENTITY_TYPE, 'App storage should have ENTITY_TYPE = restapp');
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAddFolder(): void
    {
        $storageItemResult = $this->storageService->getForApp()->storage();
        $storageId = (int)$storageItemResult->ID;
        
        $folderData = [
            'NAME' => 'Test Folder ' . time()
        ];
        
        $addFolderResult = $this->storageService->addFolder($storageId, $folderData);
        self::assertTrue($addFolderResult->getId() > 0);
        
        $folder = $addFolderResult->folder();
        self::assertEquals($folderData['NAME'], $folder->NAME);
        self::assertEquals($storageId, (int)$folder->STORAGE_ID);
        self::assertEquals('folder', $folder->TYPE);
        
        // Clean up: delete the test folder
        $folderService = Fabric::getServiceBuilder(true)->getDiskScope()->folder();
        $folderService->deleteTree($addFolderResult->getId());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetChildren(): void
    {
        $storageItemResult = $this->storageService->getForApp()->storage();
        $storageId = (int)$storageItemResult->ID;
        
        // Create a test folder first
        $folderData = [
            'NAME' => 'Test Parent Folder ' . time()
        ];
        
        $addFolderResult = $this->storageService->addFolder($storageId, $folderData);
        $parentFolderId = $addFolderResult->getId();
        
        // Get children before adding anything
        $childrenBefore = $this->storageService->getChildren($storageId)->items();
        $countBefore = count($childrenBefore);
        
        // Add a subfolder to storage root
        $subfolderData = [
            'NAME' => 'Test Subfolder ' . time()
        ];
        $subfolder = $this->storageService->addFolder($storageId, $subfolderData);
        
        // Get children after adding subfolder
        $childrenAfter = $this->storageService->getChildren($storageId)->items();
        $countAfter = count($childrenAfter);
        
        self::assertEquals($countBefore + 1, $countAfter, 'Should have one more child after adding folder');
        
        // Check that we can get folders and files separately
        $folders = $this->storageService->getChildren($storageId)->folders();
        $files = $this->storageService->getChildren($storageId)->files();
        
        self::assertIsArray($folders);
        self::assertIsArray($files);
        self::assertEquals(count($folders) + count($files), count($childrenAfter));
        
        // Clean up
        $folderService = Fabric::getServiceBuilder(true)->getDiskScope()->folder();
        $folderService->deleteTree($parentFolderId);
        $folderService->deleteTree($subfolder->getId());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testUploadFile(): void
    {
        $storageItemResult = $this->storageService->getForApp()->storage();
        $storageId = (int)$storageItemResult->ID;
        
        $fileContent = base64_encode('Test file content for upload');
        $fileData = [
            'NAME' => 'test_upload_' . time() . '.txt'
        ];
        
        $uploadFileResult = $this->storageService->uploadFile($storageId, $fileContent, $fileData);
        self::assertTrue($uploadFileResult->isSuccess(), 'File upload should be successful');
        
        $fileItemResult = $uploadFileResult->file();
        self::assertEquals($fileData['NAME'], $fileItemResult->NAME);
        self::assertEquals($storageId, (int)$fileItemResult->STORAGE_ID);
        self::assertEquals('file', $fileItemResult->TYPE);
        
        // Clean up: delete the uploaded file
        $fileService = Fabric::getServiceBuilder(true)->getDiskScope()->file();
        $fileService->markDeleted((int)$fileItemResult->ID);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testUploadFileWithUniqueNameGeneration(): void
    {
        $storageItemResult = $this->storageService->getForApp()->storage();
        $storageId = (int)$storageItemResult->ID;
        
        $fileContent = base64_encode('Test file content');
        $nameOnly = 'duplicate_test_' . time();
        $fileName = $nameOnly . '.txt';
        $fileData = [
            'NAME' => $fileName
        ];
        
        // Upload first file
        $uploadFileResult = $this->storageService->uploadFile($storageId, $fileContent, $fileData);
        self::assertTrue($uploadFileResult->isSuccess());
        
        // Upload second file with same name but unique name generation enabled
        $result2 = $this->storageService->uploadFile($storageId, $fileContent, $fileData, true);
        self::assertTrue($result2->isSuccess());
        
        $fileItemResult = $uploadFileResult->file();
        $file2 = $result2->file();
        
        // Names should be different due to unique name generation
        self::assertNotEquals($fileItemResult->NAME, $file2->NAME);
        self::assertStringContainsString($nameOnly, $file2->NAME);
        
        // Clean up
        $fileService = Fabric::getServiceBuilder(true)->getDiskScope()->file();
        $fileService->markDeleted((int)$fileItemResult->ID);
        $fileService->markDeleted((int)$file2->ID);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testRename(): void
    {
        // Test can only be performed on app storage as only app storage can be renamed
        $storageItemResult = $this->storageService->getForApp()->storage();
        $storageId = (int)$storageItemResult->ID;
        $originalName = $storageItemResult->NAME;
        
        $newName = 'Renamed Storage ' . time();
        
        $storageResult = $this->storageService->rename($storageId, $newName);
        $renamedStorage = $storageResult->storage();
        
        self::assertEquals($newName, $renamedStorage->NAME);
        self::assertEquals($storageId, (int)$renamedStorage->ID);
        
        // Restore original name
        $this->storageService->rename($storageId, $originalName);
    }
}