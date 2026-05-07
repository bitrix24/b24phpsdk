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

namespace Bitrix24\SDK\Tests\Integration\Services\Lists\Lists\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Lists\Lists\Result\ListItemResult;
use Bitrix24\SDK\Services\Lists\Lists\Service\Lists;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;
use Bitrix24\SDK\Core;

/**
 * Class ListsTest
 *
 * Integration tests for Lists service
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Lists\Lists\Service
 */
#[CoversClass(Lists::class)]
#[CoversMethod(Lists::class, 'add')]
#[CoversMethod(Lists::class, 'delete')]
#[CoversMethod(Lists::class, 'get')]
#[CoversMethod(Lists::class, 'update')]
#[CoversMethod(Lists::class, 'getIBlockTypeId')]
class ListsTest extends TestCase
{
    use CustomBitrix24Assertions;

    private Lists $listsService;

    /**
     * Set up test environment
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->listsService = Factory::getServiceBuilder()->getListsScope()->lists();
    }

    /**
     * Test create, read, update, delete list operations
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testCrudOperations(): void
    {
        $uniqueCode = 'test_list_' . (int)(microtime(true) * 1000000);
        $listFields = [
            'NAME' => 'Test List for SDK Integration',
            'DESCRIPTION' => 'Test list created by SDK integration tests',
            'SORT' => 100,
            'BIZPROC' => 'N'
        ];

        // Test list creation
        $addedItemResult = $this->listsService->add(
            'lists',
            $uniqueCode,
            $listFields
        );

        $this->assertIsInt($addedItemResult->getId());
        $this->assertGreaterThan(0, $addedItemResult->getId());
        $listId = $addedItemResult->getId();

        try {
            // Test list retrieval by code
            $getResult = $this->listsService->get(
                'lists',
                null,
                $uniqueCode
            );

            $lists = $getResult->getLists();
            $this->assertNotEmpty($lists);
            $this->assertInstanceOf(ListItemResult::class, $lists[0]);
            $this->assertEquals($listFields['NAME'], $lists[0]->NAME);
            $this->assertEquals($listFields['DESCRIPTION'], $lists[0]->DESCRIPTION);
            $this->assertEquals($uniqueCode, $lists[0]->IBLOCK_CODE);

            // Test list retrieval by ID
            $getByIdResult = $this->listsService->get(
                'lists',
                $listId
            );

            $listsById = $getByIdResult->getLists();
            $this->assertNotEmpty($listsById);
            $this->assertEquals($listId, (int)$listsById[0]->ID);

            // Test list update
            $updateFields = [
                'NAME' => 'Updated Test List for SDK Integration',
                'DESCRIPTION' => 'Updated test list description',
                'SORT' => 200
            ];

            $updateResult = $this->listsService->update(
                'lists',
                $updateFields,
                $listId
            );

            $this->assertTrue($updateResult->isSuccess());

            // Verify update was successful
            $verifyUpdateResult = $this->listsService->get(
                'lists',
                $listId
            );

            $updatedLists = $verifyUpdateResult->getLists();
            $this->assertNotEmpty($updatedLists);
            $this->assertEquals($updateFields['NAME'], $updatedLists[0]->NAME);
            $this->assertEquals($updateFields['DESCRIPTION'], $updatedLists[0]->DESCRIPTION);

        } finally {
            // Clean up: delete the test list
            $deleteResult = $this->listsService->delete(
                'lists',
                $listId
            );

            $this->assertTrue($deleteResult->isSuccess());
        }
    }

    /**
     * Test getting multiple lists
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetMultipleLists(): void
    {
        $lists = [];
        $uniquePrefix = 'test_multi_' . (int)(microtime(true) * 1000000);

        try {
            // Create multiple test lists
            for ($i = 1; $i <= 3; $i++) {
                $listCode = $uniquePrefix . '_' . $i;
                $listFields = [
                    'NAME' => 'Test List ' . $i,
                    'DESCRIPTION' => 'Test list ' . $i . ' for multi-list test',
                    'SORT' => 100 + $i,
                    'BIZPROC' => 'N'
                ];

                $addResult = $this->listsService->add(
                    'lists',
                    $listCode,
                    $listFields
                );

                $lists[] = $addResult->getId();
            }

            // Test getting all lists of type 'lists'
            $getAllResult = $this->listsService->get('lists');
            $allLists = $getAllResult->getLists();

            // Should contain at least our test lists
            $this->assertGreaterThanOrEqual(3, count($allLists));

            // Check that our test lists are in the result
            $testListNames = array_map(fn($list) => $list->NAME, $allLists);

            $this->assertContains('Test List 1', $testListNames);
            $this->assertContains('Test List 2', $testListNames);
            $this->assertContains('Test List 3', $testListNames);

        } finally {
            // Clean up: delete all test lists
            foreach ($lists as $list) {
                try {
                    $this->listsService->delete(
                        'lists',
                        $list
                    );
                } catch (\Exception) {
                    // Ignore cleanup errors
                }
            }
        }
    }

    /**
     * Test getting information block type ID
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetIBlockTypeId(): void
    {
        // Create a list first to get a valid IBLOCK_ID for testing
        $uniqueCode = 'test_list_' . (int)(microtime(true) * 1000000);
        $listFields = [
            'NAME' => 'Test List for Type ID',
            'DESCRIPTION' => 'Test list for type ID testing',
            'SORT' => 100,
            'BIZPROC' => 'N'
        ];

        $addedItemResult = $this->listsService->add(
            'lists',
            $uniqueCode,
            $listFields
        );
        $listId = $addedItemResult->getId();

        try {
            // Test with IBLOCK_ID
            $blockTypeIdResult = $this->listsService->getIBlockTypeId($listId);
            $iblockTypeId = $blockTypeIdResult->getIBlockTypeId();

            $this->assertIsString($iblockTypeId);
            $this->assertNotEmpty($iblockTypeId);
            $this->assertEquals('lists', $iblockTypeId);

            // Test with IBLOCK_CODE
            $blockTypeIdResult2 = $this->listsService->getIBlockTypeId(null, $uniqueCode);
            $iblockTypeId2 = $blockTypeIdResult2->getIBlockTypeId();

            $this->assertIsString($iblockTypeId2);
            $this->assertNotEmpty($iblockTypeId2);
            $this->assertEquals('lists', $iblockTypeId2);

            // Test that both methods return the same value
            $this->assertEquals($iblockTypeId, $iblockTypeId2);
        } finally {
            // Clean up
            $this->listsService->delete('lists', $listId);
        }
    }

    /**
     * Test list creation with permissions
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testCreateListWithPermissions(): void
    {
        $uniqueCode = 'test_permissions_' . (int)(microtime(true) * 1000000);
        $listFields = [
            'NAME' => 'Test List with Permissions',
            'DESCRIPTION' => 'Test list with custom permissions',
            'SORT' => 150,
            'BIZPROC' => 'Y'
        ];

        // Get current user ID for permissions
        $userService = Factory::getServiceBuilder()->getUserScope()->user();
        $userResult = $userService->current();
        $userId = $userResult->user()->ID;

        $permissions = [
            'U' . $userId => 'X', // Full access for current user
            '*' => 'R'            // Read access for all users
        ];

        $messages = [
            'ELEMENTS_NAME' => 'Test Items',
            'ELEMENT_NAME' => 'Test Item',
            'ELEMENT_ADD' => 'Add Test Item',
            'ELEMENT_EDIT' => 'Edit Test Item',
            'ELEMENT_DELETE' => 'Delete Test Item'
        ];

        $addedItemResult = $this->listsService->add(
            'lists',
            $uniqueCode,
            $listFields,
            $messages,
            $permissions
        );

        $this->assertIsInt($addedItemResult->getId());
        $this->assertGreaterThan(0, $addedItemResult->getId());
        $listId = $addedItemResult->getId();

        try {
            // Verify the list was created with proper settings
            $getResult = $this->listsService->get(
                'lists',
                $listId
            );

            $lists = $getResult->getLists();
            $this->assertNotEmpty($lists);
            $list = $lists[0];

            $this->assertEquals($listFields['NAME'], $list->NAME);
            $this->assertEquals($listFields['DESCRIPTION'], $list->DESCRIPTION);
            $this->assertEquals('Y', $list->BIZPROC);

        } finally {
            // Clean up
            $this->listsService->delete(
                'lists',
                $listId
            );
        }
    }

    /**
     * Test error handling for invalid operations
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testErrorHandling(): void
    {
        $this->expectException(BaseException::class);

        // Try to get a non-existent list
        $this->listsService->get(
            'lists',
            999999
        );
    }
}
