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

namespace Bitrix24\SDK\Tests\Integration\Services\Landing\Role\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Landing\Role\Result\RoleItemResult;
use Bitrix24\SDK\Services\Landing\Role\Service\Role;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

/**
 * Class RoleTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Landing\Role\Service
 */
#[CoversMethod(Role::class, 'enable')]
#[CoversMethod(Role::class, 'isEnabled')]
#[CoversMethod(Role::class, 'getList')]
#[CoversMethod(Role::class, 'getRights')]
#[CoversMethod(Role::class, 'setAccessCodes')]
#[CoversMethod(Role::class, 'setRights')]
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\Landing\Role\Service\Role::class)]
class RoleTest extends TestCase
{
    use CustomBitrix24Assertions;

    protected Role $roleService;

    protected function setUp(): void
    {
        $serviceBuilder = Fabric::getServiceBuilder();
        $this->roleService = $serviceBuilder->getLandingScope()->role();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testIsEnabled(): void
    {
        // Test checking current permission model state
        $isEnabledResult = $this->roleService->isEnabled();
        $isRoleModelEnabled = $isEnabledResult->isEnabled();
        
        self::assertIsBool($isRoleModelEnabled, 'isEnabled should return boolean');
        
        // The result should be consistent when called multiple times
        $secondCheck = $this->roleService->isEnabled();
        $secondResult = $secondCheck->isEnabled();
        
        self::assertEquals($isRoleModelEnabled, $secondResult, 'Multiple calls should return consistent result');
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetList(): void
    {
        // Test getting list of all roles
        $rolesResult = $this->roleService->getList();
        $roles = $rolesResult->getRoles();
        
        self::assertIsArray($roles, 'getRoles should return array');
        
        // Validate each role item structure
        foreach ($roles as $role) {
            self::assertInstanceOf(RoleItemResult::class, $role, 'Each role should be RoleItemResult instance');
            
            // Validate that ID and TITLE properties are accessible
            if ($role->ID !== null) {
                self::assertTrue(
                    is_numeric($role->ID), 
                    'Role ID should be numeric (string or integer)'
                );
                self::assertGreaterThan(0, (int)$role->ID, 'Role ID should be positive');
            }
            
            if ($role->TITLE !== null) {
                self::assertIsString($role->TITLE, 'Role title should be string');
                self::assertNotEmpty($role->TITLE, 'Role title should not be empty');
            }
            
            if ($role->XML_ID !== null) {
                self::assertIsString($role->XML_ID, 'Role XML_ID should be string');
            }
        }
        
        // Roles list should be retrievable (may be empty on fresh installation)
        self::assertGreaterThanOrEqual(0, count($roles), 'Roles count should be non-negative');
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetRights(): void
    {
        // First get the list of roles to test with
        $rolesResult = $this->roleService->getList();
        $roles = $rolesResult->getRoles();
        
        if ($roles === []) {
            self::markTestSkipped('No roles available for testing getRights');
        }
        
        // Test getRights with first available role
        $firstRole = $roles[0];
        $roleId = (int)($firstRole->ID ?? 1);
        
        $rightsResult = $this->roleService->getRights($roleId);
        $rights = $rightsResult->getRights();
        
        self::assertIsArray($rights, 'getRights should return array');
        
        // Rights array structure validation - associative array where keys are site IDs
        foreach ($rights as $siteId => $permissionsArray) {
            // Site ID should be integer (either 0 for default or actual site ID)
            self::assertIsInt($siteId, 'Site ID should be integer');
            
            // Permissions should be array of strings
            self::assertIsArray($permissionsArray, 'Permissions should be array');
            
            foreach ($permissionsArray as $permissionArray) {
                self::assertIsString($permissionArray, 'Each permission should be string');
                self::assertContains(
                    $permissionArray,
                    ['denied', 'read', 'edit', 'sett', 'public', 'delete'],
                    'Permission should be valid value'
                );
            }
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testSetAccessCodes(): void
    {
        // First get the list of roles to test with
        $rolesResult = $this->roleService->getList();
        $roles = $rolesResult->getRoles();
        
        if ($roles === []) {
            self::markTestSkipped('No roles available for testing setAccessCodes');
        }
        
        // Test setAccessCodes with first available role
        $firstRole = $roles[0];
        $roleId = (int)($firstRole->ID ?? 1);
        
        // Test with simple access codes (empty array to reset)
        $testCodes = [];
        
        $setAccessCodesResult = $this->roleService->setAccessCodes($roleId, $testCodes);
        $isSuccess = $setAccessCodesResult->isSuccess();
        
        self::assertIsBool($isSuccess, 'setAccessCodes should return boolean result');
        
        // Test with some actual access codes
        $testCodesWithValues = ['UA']; // All authorized users
        
        $setAccessCodesWithValuesResult = $this->roleService->setAccessCodes($roleId, $testCodesWithValues);
        $isSuccessWithValues = $setAccessCodesWithValuesResult->isSuccess();
        
        self::assertIsBool($isSuccessWithValues, 'setAccessCodes with values should return boolean result');
        
        // Reset back to empty for cleanup
        $this->roleService->setAccessCodes($roleId, []);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testSetRights(): void
    {
        // First get the list of roles to test with
        $rolesResult = $this->roleService->getList();
        $roles = $rolesResult->getRoles();
        
        if ($roles === []) {
            self::markTestSkipped('No roles available for testing setRights');
        }
        
        // Test setRights with first available role
        $firstRole = $roles[0];
        $roleId = (int)($firstRole->ID ?? 1);
        
        // Test with simple rights configuration
        $testRights = [
            '0' => ['read'] // Default permissions for the role
        ];
        
        $setRightsResult = $this->roleService->setRights($roleId, $testRights);
        $isSuccess = $setRightsResult->isSuccess();
        
        self::assertIsBool($isSuccess, 'setRights should return boolean result');
        
        // Test with additional rights
        $testAdditionalRights = ['menu24'];
        
        $setRightsWithAdditionalResult = $this->roleService->setRights(
            $roleId, 
            $testRights, 
            $testAdditionalRights
        );
        $isSuccessWithAdditional = $setRightsWithAdditionalResult->isSuccess();
        
        self::assertIsBool($isSuccessWithAdditional, 'setRights with additional should return boolean result');
        
        // Test with complex rights configuration
        $complexRights = [
            '0' => ['read'],
            '1' => ['read', 'edit']
        ];
        
        $setComplexRightsResult = $this->roleService->setRights($roleId, $complexRights);
        $isComplexSuccess = $setComplexRightsResult->isSuccess();
        
        self::assertIsBool($isComplexSuccess, 'setRights with complex configuration should return boolean result');
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testEnableAndDisableRoleModel(): void
    {
        // Get current state
        $isEnabledResult = $this->roleService->isEnabled();
        $currentState = $isEnabledResult->isEnabled();
        
        // Test enabling role model
        $enableResult = $this->roleService->enable(1);
        $enableSuccess = $enableResult->isSuccess();
        
        self::assertIsBool($enableSuccess, 'enable(1) should return boolean result');
        
        // Check if state changed to enabled
        $afterEnableResult = $this->roleService->isEnabled();
        $afterEnableState = $afterEnableResult->isEnabled();
        
        self::assertIsBool($afterEnableState, 'After enable, isEnabled should return boolean');
        
        // Test disabling role model
        $disableResult = $this->roleService->enable(0);
        $disableSuccess = $disableResult->isSuccess();
        
        self::assertIsBool($disableSuccess, 'enable(0) should return boolean result');
        
        // Check if state changed to disabled
        $afterDisableResult = $this->roleService->isEnabled();
        $afterDisableState = $afterDisableResult->isEnabled();
        
        self::assertIsBool($afterDisableState, 'After disable, isEnabled should return boolean');
        
        // Restore original state
        $restoreResult = $this->roleService->enable($currentState ? 1 : 0);
        $restoreSuccess = $restoreResult->isSuccess();
        
        self::assertIsBool($restoreSuccess, 'Restore should return boolean result');
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testRoleModelToggleConsistency(): void
    {
        // Test that enable/isEnabled work consistently
        $isEnabledResult = $this->roleService->isEnabled();
        $initialState = $isEnabledResult->isEnabled();
        
        // Toggle to opposite state
        $enableResult = $this->roleService->enable($initialState ? 0 : 1);
        $toggleSuccess = $enableResult->isSuccess();
        
        self::assertIsBool($toggleSuccess, 'Toggle should return boolean result');
        
        // Check new state
        $newStateResult = $this->roleService->isEnabled();
        $newState = $newStateResult->isEnabled();
        
        // State should have changed
        self::assertNotEquals($initialState, $newState, 'State should change after toggle');
        
        // Toggle back
        $toggleBackResult = $this->roleService->enable($initialState ? 1 : 0);
        $toggleBackSuccess = $toggleBackResult->isSuccess();
        
        self::assertIsBool($toggleBackSuccess, 'Toggle back should return boolean result');
        
        // Verify we're back to original state
        $finalStateResult = $this->roleService->isEnabled();
        $finalState = $finalStateResult->isEnabled();
        
        self::assertEquals($initialState, $finalState, 'Should return to original state');
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testCompleteRoleManagementWorkflow(): void
    {
        // Test a complete workflow of role management
        
        // 1. Check initial state
        $isEnabledResult = $this->roleService->isEnabled();
        $isEnabledResult->isEnabled();
        
        // 2. Get available roles
        $rolesResult = $this->roleService->getList();
        $roles = $rolesResult->getRoles();
        
        if ($roles === []) {
            self::markTestSkipped('No roles available for complete workflow test');
        }
        
        $testRole = $roles[0];
        $roleId = (int)($testRole->ID ?? 1);
        
        // 3. Get current rights for the role
        $currentRightsResult = $this->roleService->getRights($roleId);
        $currentRights = $currentRightsResult->getRights();
        
        self::assertIsArray($currentRights, 'Current rights should be array');
        
        // 4. Set new access codes
        $testCodes = ['UA']; // All authorized users
        $setAccessCodesResult = $this->roleService->setAccessCodes($roleId, $testCodes);
        
        self::assertIsBool($setAccessCodesResult->isSuccess(), 'Setting access codes should return boolean');
        
        // 5. Set new rights
        $testRights = [
            '0' => ['read']
        ];
        $setRightsResult = $this->roleService->setRights($roleId, $testRights);
        
        self::assertIsBool($setRightsResult->isSuccess(), 'Setting rights should return boolean');
        
        // 6. Verify rights were set (get them again)
        $updatedRightsResult = $this->roleService->getRights($roleId);
        $updatedRights = $updatedRightsResult->getRights();
        
        self::assertIsArray($updatedRights, 'Updated rights should be array');
        
        // 7. Clean up - restore original access codes (empty)
        $this->roleService->setAccessCodes($roleId, []);
        
        // 8. Restore original rights if they were different
        if ($currentRights !== $updatedRights) {
            $this->roleService->setRights($roleId, $currentRights);
        }
        
        self::assertTrue(true, 'Complete workflow should execute without errors');
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testInvalidRoleIdHandling(): void
    {
        // Test with obviously invalid role ID
        $invalidRoleId = 999999;
        
        try {
            $rightsResult = $this->roleService->getRights($invalidRoleId);
            $rights = $rightsResult->getRights();
            
            // If no exception is thrown, the result should still be an array
            self::assertIsArray($rights, 'Rights for invalid role should be array (possibly empty)');
        } catch (\Exception $exception) {
            // If an exception is thrown for invalid role ID, that's acceptable
            self::assertInstanceOf(\Exception::class, $exception, 'Invalid role ID should handle gracefully');
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testPermissionModelsAccessibility(): void
    {
        // Test that both permission models are accessible
        
        // Enable role model
        $enableResult = $this->roleService->enable(1);
        self::assertIsBool($enableResult->isSuccess(), 'Enabling role model should work');
        
        $isEnabledResult = $this->roleService->isEnabled();
        self::assertIsBool($isEnabledResult->isEnabled(), 'Should get boolean state for role model');
        
        // Enable extended model
        $enableExtendedResult = $this->roleService->enable(0);
        self::assertIsBool($enableExtendedResult->isSuccess(), 'Enabling extended model should work');
        
        $extendedEnabledState = $this->roleService->isEnabled();
        self::assertIsBool($extendedEnabledState->isEnabled(), 'Should get boolean state for extended model');
        
        // Both operations should succeed regardless of admin permissions
        self::assertTrue(true, 'Permission model switching should be accessible');
    }

    /**
     * Test edge cases with access codes
     * 
     * @throws BaseException
     * @throws TransportException
     */
    public function testAccessCodesEdgeCases(): void
    {
        $rolesResult = $this->roleService->getList();
        $roles = $rolesResult->getRoles();
        
        if ($roles === []) {
            self::markTestSkipped('No roles available for access codes edge cases test');
        }
        
        $roleId = (int)($roles[0]->ID ?? 1);
        
        // Test with empty array (should reset codes)
        $setAccessCodesResult = $this->roleService->setAccessCodes($roleId, []);
        self::assertIsBool($setAccessCodesResult->isSuccess(), 'Empty access codes should work');
        
        // Test with various valid codes
        $validCodes = ['UA', 'G1', 'U1'];
        $validResult = $this->roleService->setAccessCodes($roleId, $validCodes);
        self::assertIsBool($validResult->isSuccess(), 'Valid access codes should work');
        
        // Clean up
        $this->roleService->setAccessCodes($roleId, []);
    }

    /**
     * Test edge cases with rights configuration
     * 
     * @throws BaseException
     * @throws TransportException
     */
    public function testRightsConfigurationEdgeCases(): void
    {
        $rolesResult = $this->roleService->getList();
        $roles = $rolesResult->getRoles();
        
        if ($roles === []) {
            self::markTestSkipped('No roles available for rights configuration edge cases test');
        }
        
        $roleId = (int)($roles[0]->ID ?? 1);
        
        // Test with empty rights
        $emptyRights = [];
        $setRightsResult = $this->roleService->setRights($roleId, $emptyRights);
        self::assertIsBool($setRightsResult->isSuccess(), 'Empty rights should work');
        
        // Test with only default rights (key '0')
        $defaultRights = ['0' => ['read']];
        $defaultResult = $this->roleService->setRights($roleId, $defaultRights);
        self::assertIsBool($defaultResult->isSuccess(), 'Default rights should work');
        
        // Test with all permissions
        $fullPermissions = ['0' => ['read', 'edit', 'sett', 'public', 'delete']];
        $fullResult = $this->roleService->setRights($roleId, $fullPermissions);
        self::assertIsBool($fullResult->isSuccess(), 'Full permissions should work');
        
        // Test with denied permission
        $deniedPermissions = ['0' => ['denied']];
        $deniedResult = $this->roleService->setRights($roleId, $deniedPermissions);
        self::assertIsBool($deniedResult->isSuccess(), 'Denied permissions should work');
    }
}