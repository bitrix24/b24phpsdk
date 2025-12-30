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

namespace Bitrix24\SDK\Tests\Integration\Services\SonetGroup\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\SonetGroup\Result\SonetGroupGetItemResult;
use Bitrix24\SDK\Services\SonetGroup\Result\SonetGroupListItemResult;
use Bitrix24\SDK\Services\SonetGroup\Service\SonetGroup;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

/**
 * Class SonetGroupTest
 *
 * Integration tests for Social Network Groups service
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\SonetGroup\Service
 */
#[CoversClass(SonetGroup::class)]
#[CoversMethod(SonetGroup::class, 'create')]
#[CoversMethod(SonetGroup::class, 'delete')]
#[CoversMethod(SonetGroup::class, 'get')]
#[CoversMethod(SonetGroup::class, 'list')]
#[CoversMethod(SonetGroup::class, 'getGroups')]
#[CoversMethod(SonetGroup::class, 'update')]
#[CoversMethod(SonetGroup::class, 'getUserGroups')]
#[CoversMethod(SonetGroup::class, 'addUser')]
#[CoversMethod(SonetGroup::class, 'deleteUser')]
#[CoversMethod(SonetGroup::class, 'setOwner')]
class SonetGroupTest extends TestCase
{
    use CustomBitrix24Assertions;

    private SonetGroup $sonetGroupService;

    /**
     * Helper method to get current user ID
     *
     * @throws BaseException
     * @throws TransportException
     */
    private function getCurrentUserId(): int
    {
        $userService = Fabric::getServiceBuilder()->getUserScope()->user();
        $userResult = $userService->current();
        
        return $userResult->user()->ID;
    }

    /**
     * Helper method to delete a test group
     */
    private function deleteTestGroup(int $id): void
    {
        try {
            $this->sonetGroupService->delete($id);
        } catch (\Exception) {
            // Ignore if group doesn't exist
        }
    }

    /**
     * Test create social network group
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testCreate(): void
    {
        $groupName = 'Test SonetGroup ' . time();
        
        $addedItemResult = $this->sonetGroupService->create([
            'NAME' => $groupName,
            'DESCRIPTION' => 'Test social network group description',
            'VISIBLE' => 'Y',
            'OPENED' => 'N',
            'INITIATE_PERMS' => 'K',
            'SPAM_PERMS' => 'K'
        ]);

        self::assertGreaterThanOrEqual(1, $addedItemResult->getId());

        // Clean up
        $this->deleteTestGroup($addedItemResult->getId());
    }

    /**
     * Test create project
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testCreateProject(): void
    {
        $projectName = 'Test Project ' . time();
        
        $addedItemResult = $this->sonetGroupService->create([
            'NAME' => $projectName,
            'DESCRIPTION' => 'Test project description',
            'VISIBLE' => 'Y',
            'OPENED' => 'N',
            'INITIATE_PERMS' => 'K',
            'SPAM_PERMS' => 'K',
            'PROJECT' => 'Y',
            'PROJECT_DATE_START' => date('Y-m-d'),
            'PROJECT_DATE_FINISH' => date('Y-m-d', strtotime('+30 days'))
        ]);

        self::assertGreaterThanOrEqual(1, $addedItemResult->getId());

        // Clean up
        $this->deleteTestGroup($addedItemResult->getId());
    }

    /**
     * Test delete social network group
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelete(): void
    {
        $groupName = 'Test Group to Delete ' . time();
        
        $addedItemResult = $this->sonetGroupService->create([
            'NAME' => $groupName,
            'DESCRIPTION' => 'Test group for deletion',
            'VISIBLE' => 'Y',
            'OPENED' => 'N',
            'INITIATE_PERMS' => 'K',
            'SPAM_PERMS' => 'K'
        ]);

        $deletedItemResult = $this->sonetGroupService->delete($addedItemResult->getId());
        self::assertTrue($deletedItemResult->isSuccess());
    }

    /**
     * Test get detailed group information
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testGet(): void
    {
        // Create a test group first
        $groupName = 'Test Group for Get ' . time();
        
        $addedItemResult = $this->sonetGroupService->create([
            'NAME' => $groupName,
            'DESCRIPTION' => 'Test group for getting detailed info',
            'VISIBLE' => 'Y',
            'OPENED' => 'N',
            'INITIATE_PERMS' => 'K',
            'SPAM_PERMS' => 'K'
        ]);

        $groupId = $addedItemResult->getId();

        // Get group with additional fields
        $sonetGroupResult = $this->sonetGroupService->get($groupId, [
            'OWNER_DATA',
            'ACTIONS',
            'USER_DATA',
            'TAGS'
        ]);

        $sonetGroupGetItemResult = $sonetGroupResult->getGroup();
        self::assertInstanceOf(SonetGroupGetItemResult::class, $sonetGroupGetItemResult);
        self::assertEquals($groupId, $sonetGroupGetItemResult->ID);
        self::assertEquals($groupName, $sonetGroupGetItemResult->NAME);

        // Clean up
        $this->deleteTestGroup($groupId);
    }

    /**
     * Test list groups using socialnetwork.api.workgroup.list
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testList(): void
    {
        // Create a test group first
        $groupName = 'Test Group for List ' . time();
        
        $addedItemResult = $this->sonetGroupService->create([
            'NAME' => $groupName,
            'DESCRIPTION' => 'Test group for listing',
            'VISIBLE' => 'Y',
            'OPENED' => 'N',
            'INITIATE_PERMS' => 'K',
            'SPAM_PERMS' => 'K'
        ]);

        $groupId = $addedItemResult->getId();

        $sonetGroupsResult = $this->sonetGroupService->list(
            ['ID' => $groupId],
            ['ID', 'SITE_ID', 'NAME', 'DESCRIPTION', 'DATE_CREATE', 'DATE_UPDATE', 'DATE_ACTIVITY', 'ACTIVE', 'VISIBLE', 'OPENED', 'CLOSED', 'SUBJECT_ID', 'OWNER_ID', 'KEYWORDS', 'IMAGE_ID', 'NUMBER_OF_MEMBERS', 'INITIATE_PERMS', 'SPAM_PERMS', 'SUBJECT_NAME'],
            false
        );
           // ['ID', 'NAME', 'ACTIVE'],

        self::assertGreaterThanOrEqual(1, count($sonetGroupsResult->getGroups()));
        
        foreach ($sonetGroupsResult->getGroups() as $sonetGroupListItemResult) {
            self::assertInstanceOf(SonetGroupListItemResult::class, $sonetGroupListItemResult);
            self::assertNotNull($sonetGroupListItemResult->id);
            self::assertNotNull($sonetGroupListItemResult->name);
        }

        // Clean up
        $this->deleteTestGroup($groupId);
    }

    /**
     * Test list groups using sonet_group.get
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetGroups(): void
    {
        // Create a test group first
        $groupName = 'Test Group for GetGroups ' . time();
        
        $addedItemResult = $this->sonetGroupService->create([
            'NAME' => $groupName,
            'DESCRIPTION' => 'Test group for getting groups',
            'VISIBLE' => 'Y',
            'OPENED' => 'N',
            'INITIATE_PERMS' => 'K',
            'SPAM_PERMS' => 'K'
        ]);

        $groupId = $addedItemResult->getId();

        $sonetGetGroupsResult = $this->sonetGroupService->getGroups(
            ['NAME' => 'ASC'],
            ['%NAME' => substr($groupName, 0, 10)],
            false
        );

        self::assertGreaterThanOrEqual(1, count($sonetGetGroupsResult->getGroups()));
        
        foreach ($sonetGetGroupsResult->getGroups() as $sonetGroupGetItemResult) {
            self::assertInstanceOf(SonetGroupGetItemResult::class, $sonetGroupGetItemResult);
            self::assertNotNull($sonetGroupGetItemResult->ID);
            self::assertNotNull($sonetGroupGetItemResult->NAME);
        }

        // Clean up
        $this->deleteTestGroup($groupId);
    }

    /**
     * Test update group
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testUpdate(): void
    {
        // Create a group first
        $addedItemResult = $this->sonetGroupService->create([
            'NAME' => 'Group for Update Test ' . time(),
            'DESCRIPTION' => 'Original description',
            'VISIBLE' => 'Y',
            'OPENED' => 'N',
            'INITIATE_PERMS' => 'K',
            'SPAM_PERMS' => 'K'
        ]);

        $groupId = $addedItemResult->getId();
        $newName = 'Updated Group ' . time();

        // Update the group
        $updatedItemResult = $this->sonetGroupService->update($groupId, [
            'NAME' => $newName,
            'DESCRIPTION' => 'Updated description'
        ]);

        self::assertTrue($updatedItemResult->isSuccess());

        // Verify the update
        $sonetGroupResult = $this->sonetGroupService->get($groupId);
        $sonetGroupGetItemResult = $sonetGroupResult->getGroup();
        self::assertEquals($newName, $sonetGroupGetItemResult->NAME);

        // Clean up
        $this->deleteTestGroup($groupId);
    }

    /**
     * Test get current user's groups
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetUserGroups(): void
    {
        // Create a test group first
        $groupName = 'Test Group for User Groups ' . time();
        
        $addedItemResult = $this->sonetGroupService->create([
            'NAME' => $groupName,
            'DESCRIPTION' => 'Test group for user groups',
            'VISIBLE' => 'Y',
            'OPENED' => 'N',
            'INITIATE_PERMS' => 'K',
            'SPAM_PERMS' => 'K'
        ]);

        $groupId = $addedItemResult->getId();

        $userGroupsResult = $this->sonetGroupService->getUserGroups();
        
        self::assertGreaterThanOrEqual(1, count($userGroupsResult->getUserGroups()));
        
        // Check if our test group is in the list
        $found = false;
        foreach ($userGroupsResult->getUserGroups() as $userGroupItemResult) {
            self::assertInstanceOf(\Bitrix24\SDK\Services\SonetGroup\Result\UserGroupItemResult::class, $userGroupItemResult);
            self::assertNotNull($userGroupItemResult->GROUP_ID);
            self::assertNotNull($userGroupItemResult->GROUP_NAME);
            self::assertNotNull($userGroupItemResult->ROLE);
            
            if ($userGroupItemResult->GROUP_ID == $groupId) {
                $found = true;
                self::assertEquals('A', $userGroupItemResult->ROLE); // Owner role
            }
        }
        
        self::assertTrue($found, 'Created test group should be in user groups list');

        // Clean up
        $this->deleteTestGroup($groupId);
    }

    /**
     * Test add user to group
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testAddUser(): void
    {
        // Create a test group first
        $groupName = 'Test Group for Add User ' . time();
        
        $addedItemResult = $this->sonetGroupService->create([
            'NAME' => $groupName,
            'DESCRIPTION' => 'Test group for adding users',
            'VISIBLE' => 'Y',
            'OPENED' => 'N',
            'INITIATE_PERMS' => 'K',
            'SPAM_PERMS' => 'K'
        ]);

        $groupId = $addedItemResult->getId();
        $currentUserId = $this->getCurrentUserId();

        // Try to add current user (creator is automatically a member/owner)
        // This should succeed with empty result (user already a member)
        $sonetGroupUserOperationResult = $this->sonetGroupService->addUser($groupId, $currentUserId);
        
        // Should return success even if user is already a member
        self::assertTrue($sonetGroupUserOperationResult->isSuccess(), 'addUser should succeed even if user is already a member');

        // Clean up
        $this->deleteTestGroup($groupId);
    }

    /**
     * Test set group owner
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testSetOwner(): void
    {
        // Create a test group first
        $groupName = 'Test Group for Set Owner ' . time();
        
        $addedItemResult = $this->sonetGroupService->create([
            'NAME' => $groupName,
            'DESCRIPTION' => 'Test group for setting owner',
            'VISIBLE' => 'Y',
            'OPENED' => 'N',
            'INITIATE_PERMS' => 'K',
            'SPAM_PERMS' => 'K'
        ]);

        $groupId = $addedItemResult->getId();
        $currentUserId = $this->getCurrentUserId();

        // Try to set the same user as owner (should work or be already set)
        $updatedItemResult = $this->sonetGroupService->setOwner($groupId, $currentUserId);
        self::assertTrue($updatedItemResult->isSuccess());

        // Clean up
        $this->deleteTestGroup($groupId);
    }

    #[\Override]
    protected function setUp(): void
    {
        $this->sonetGroupService = Fabric::getServiceBuilder()->getSonetGroupScope()->sonetGroup();
    }

    #[\Override]
    protected function tearDown(): void
    {
        // Additional cleanup: remove any remaining test groups that might have been left
        $this->cleanupTestGroups();
    }

    /**
     * Clean up any test groups that might be left over
     */
    private function cleanupTestGroups(): void
    {
        try {
            $groupsResult = $this->sonetGroupService->getGroups(
                [],
                ['%NAME' => 'Test'],
                false
            );
            
            foreach ($groupsResult->getGroups() as $sonetGroupGetItemResult) {
                if (str_contains($sonetGroupGetItemResult->NAME, 'Test')) {
                    try {
                        $this->sonetGroupService->delete(intval($sonetGroupGetItemResult->ID));
                    } catch (BaseException $e) {
                        // Ignore individual deletion errors
                        error_log(sprintf('Warning: Failed to cleanup test group %s: ', $sonetGroupGetItemResult->NAME) . $e->getMessage());
                    }
                }
            }
        } catch (BaseException $baseException) {
            // Ignore general cleanup errors
            error_log("Warning: Failed to list groups during cleanup: " . $baseException->getMessage());
        }
    }
}
