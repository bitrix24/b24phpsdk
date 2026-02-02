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

namespace Bitrix24\SDK\Tests\Integration\Services\Lists\Field\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Lists\Field\Result\FieldItemResult;
use Bitrix24\SDK\Services\Lists\Field\Service\Field;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

/**
 * Class FieldTest
 *
 * Integration tests for Field service
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Lists\Field\Service
 */
#[CoversClass(Field::class)]
#[CoversMethod(Field::class, 'add')]
#[CoversMethod(Field::class, 'update')]
#[CoversMethod(Field::class, 'get')]
#[CoversMethod(Field::class, 'delete')]
#[CoversMethod(Field::class, 'types')]
#[CoversMethod(Field::class, 'addByCode')]
#[CoversMethod(Field::class, 'updateByCode')]
#[CoversMethod(Field::class, 'getByCode')]
#[CoversMethod(Field::class, 'deleteByCode')]
class FieldTest extends TestCase
{
    use CustomBitrix24Assertions;

    private Field $fieldService;

    private int $testListId;

    private string $testListCode;

    /**
     * Set up test environment
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->fieldService = Factory::getServiceBuilder()->getListsScope()->field();

        // Create a test list for field operations
        $uniqueCode = 'test_field_list_' . (int)(microtime(true) * 1000000);
        $listFields = [
            'NAME' => 'Test List for Field Operations',
            'DESCRIPTION' => 'Test list created for field integration tests',
            'SORT' => 100,
            'BIZPROC' => 'N'
        ];

        $addedItemResult = Factory::getServiceBuilder()->getListsScope()->lists()->add(
            'lists',
            $uniqueCode,
            $listFields
        );

        $this->testListId = $addedItemResult->getId();
        $this->testListCode = $uniqueCode;
    }

    /**
     * Clean up test environment
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function tearDown(): void
    {
        // Delete test list
        try {
            Factory::getServiceBuilder()->getListsScope()->lists()->delete(
                'lists',
                $this->testListId
            );
        } catch (\Exception) {
            // Ignore cleanup errors
        }
    }

    /**
     * Test CRUD operations for scalar field (string type)
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testScalarFieldCrudOperations(): void
    {
        $fieldCode = 'TEST_STRING_FIELD_' . (int)(microtime(true) * 1000000);

        $fieldData = [
            'NAME' => 'Test String Field',
            'TYPE' => 'S',
            'IS_REQUIRED' => 'N',
            'MULTIPLE' => 'N',
            'SORT' => 100,
            'CODE' => $fieldCode,
            'DEFAULT_VALUE' => 'Default test value',
            'SETTINGS' => [
                'SHOW_ADD_FORM' => 'Y',
                'SHOW_EDIT_FORM' => 'Y',
                'ADD_READ_ONLY_FIELD' => 'N',
                'EDIT_READ_ONLY_FIELD' => 'N',
                'SHOW_FIELD_PREVIEW' => 'N'
            ],
            'ROW_COUNT' => 1,
            'COL_COUNT' => 30
        ];

        // Test field creation
        $addedFieldResult = $this->fieldService->add(
            'lists',
            $fieldData,
            $this->testListId
        );

        $fieldId = $addedFieldResult->getId();
        $this->assertStringStartsWith('PROPERTY_', $fieldId);

        try {
            // Test field retrieval by ID
            $getResult = $this->fieldService->get(
                'lists',
                $this->testListId,
                null,
                $fieldId
            );

            $field = $getResult->field();
            $this->assertInstanceOf(FieldItemResult::class, $field);
            $this->assertEquals($fieldData['NAME'], $field->NAME);
            $this->assertEquals($fieldData['TYPE'], $field->TYPE);
            $this->assertEquals($fieldCode, $field->CODE);

            // Test field update
            $updateData = [
                'NAME' => 'Updated Test String Field',
                'TYPE' => 'S', // Type cannot be changed
                'IS_REQUIRED' => 'Y',
                'SORT' => 200,
                'DEFAULT_VALUE' => 'Updated default value'
            ];

            $updateResult = $this->fieldService->update(
                'lists',
                $fieldId,
                $updateData,
                $this->testListId
            );

            $this->assertTrue($updateResult->isSuccess());

            // Verify update was successful
            $verifyUpdateResult = $this->fieldService->get(
                'lists',
                $this->testListId,
                null,
                $fieldId
            );

            $updatedField = $verifyUpdateResult->field();
            $this->assertEquals($updateData['NAME'], $updatedField->NAME);
            $this->assertEquals($updateData['IS_REQUIRED'], $updatedField->IS_REQUIRED);

        } finally {
            // Clean up: delete the test field
            $deleteResult = $this->fieldService->delete(
                'lists',
                $fieldId,
                $this->testListId
            );

            $this->assertTrue($deleteResult->isSuccess());
        }
    }

    /**
     * Test CRUD operations for list field (list type)
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testListFieldCrudOperations(): void
    {
        $fieldCode = 'TEST_LIST_FIELD_' . (int)(microtime(true) * 1000000);

        $fieldData = [
            'NAME' => 'Test List Field',
            'TYPE' => 'L',
            'IS_REQUIRED' => 'N',
            'MULTIPLE' => 'N',
            'SORT' => 100,
            'CODE' => $fieldCode,
            'LIST' => [
                '10' => [
                    'VALUE' => 'Option 1',
                    'SORT' => 10,
                    'DEF' => 'Y'
                ],
                '20' => [
                    'VALUE' => 'Option 2',
                    'SORT' => 20,
                    'DEF' => 'N'
                ]
            ],
            'LIST_TEXT_VALUES' => "Option 3\nOption 4",
            'SETTINGS' => [
                'SHOW_ADD_FORM' => 'Y',
                'SHOW_EDIT_FORM' => 'Y',
                'ADD_READ_ONLY_FIELD' => 'N',
                'EDIT_READ_ONLY_FIELD' => 'N',
                'SHOW_FIELD_PREVIEW' => 'N'
            ]
        ];

        // Test field creation
        $addedFieldResult = $this->fieldService->add(
            'lists',
            $fieldData,
            $this->testListId
        );

        $fieldId = $addedFieldResult->getId();
        $this->assertStringStartsWith('PROPERTY_', $fieldId);

        try {
            // Test field retrieval
            $getResult = $this->fieldService->get(
                'lists',
                $this->testListId,
                null,
                $fieldId
            );

            $field = $getResult->field();
            $this->assertInstanceOf(FieldItemResult::class, $field);
            $this->assertEquals($fieldData['NAME'], $field->NAME);
            $this->assertEquals($fieldData['TYPE'], $field->TYPE);
            $this->assertEquals($fieldCode, $field->CODE);

            // Check that list values are present
            $this->assertNotEmpty($field->DISPLAY_VALUES_FORM);

            // Test field update with new list values
            $updateData = [
                'NAME' => 'Updated Test List Field',
                'TYPE' => 'L', // Type cannot be changed
                'LIST_TEXT_VALUES' => "Updated Option 1\nUpdated Option 2\nUpdated Option 3"
            ];

            $updateResult = $this->fieldService->update(
                'lists',
                $fieldId,
                $updateData,
                $this->testListId
            );

            $this->assertTrue($updateResult->isSuccess());

        } finally {
            // Clean up: delete the test field
            $deleteResult = $this->fieldService->delete(
                'lists',
                $fieldId,
                $this->testListId
            );

            $this->assertTrue($deleteResult->isSuccess());
        }
    }

    /**
     * Test getting all fields from list
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetAllFields(): void
    {
        $fieldsToCleanup = [];

        try {
            // Create multiple test fields
            for ($i = 1; $i <= 3; $i++) {
                $fieldCode = 'TEST_FIELD_' . $i . '_' . (int)(microtime(true) * 1000000);
                $fieldData = [
                    'NAME' => 'Test Field ' . $i,
                    'TYPE' => 'S',
                    'CODE' => $fieldCode,
                    'SORT' => 100 + $i
                ];

                $addResult = $this->fieldService->add(
                    'lists',
                    $fieldData,
                    $this->testListId
                );

                $fieldsToCleanup[] = $addResult->getId();
            }

            // Test getting all fields
            $getAllResult = $this->fieldService->get(
                'lists',
                $this->testListId
            );

            $allFields = $getAllResult->fields();
            $this->assertGreaterThanOrEqual(3, count($allFields));

            // Check that our test fields are in the result
            $testFieldNames = array_map(fn($field) => $field->NAME, $allFields);
            $this->assertContains('Test Field 1', $testFieldNames);
            $this->assertContains('Test Field 2', $testFieldNames);
            $this->assertContains('Test Field 3', $testFieldNames);

        } finally {
            // Clean up: delete all test fields
            foreach ($fieldsToCleanup as $fieldToCleanup) {
                try {
                    $this->fieldService->delete(
                        'lists',
                        $fieldToCleanup,
                        $this->testListId
                    );
                } catch (\Exception) {
                    // Ignore cleanup errors
                }
            }
        }
    }

    /**
     * Test getting available field types
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetFieldTypes(): void
    {
        $fieldTypesResult = $this->fieldService->types(
            'lists',
            $this->testListId
        );

        $types = $fieldTypesResult->types();

        $this->assertIsArray($types);
        $this->assertNotEmpty($types);

        // Check that common field types are present
        $this->assertArrayHasKey('S', $types); // String
        $this->assertArrayHasKey('N', $types); // Number
        $this->assertArrayHasKey('L', $types); // List
        $this->assertArrayHasKey('F', $types); // File

        // Check that system field types are present
        $this->assertArrayHasKey('SORT', $types);
        $this->assertArrayHasKey('ACTIVE_FROM', $types);
        $this->assertArrayHasKey('ACTIVE_TO', $types);
    }

    /**
     * Test helper methods with iblock code
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testHelperMethodsWithCode(): void
    {
        $fieldCode = 'TEST_HELPER_FIELD_' . (int)(microtime(true) * 1000000);

        $fieldData = [
            'NAME' => 'Test Helper Field',
            'TYPE' => 'S',
            'CODE' => $fieldCode,
            'SORT' => 100
        ];

        // Test addByCode
        $addedFieldResult = $this->fieldService->addByCode(
            'lists',
            $this->testListCode,
            $fieldData
        );

        $fieldId = $addedFieldResult->getId();
        $this->assertStringStartsWith('PROPERTY_', $fieldId);

        try {
            // Test getByCode
            $getResult = $this->fieldService->getByCode(
                'lists',
                $this->testListCode,
                $fieldId
            );

            $field = $getResult->field();
            $this->assertEquals($fieldData['NAME'], $field->NAME);

            // Test updateByCode
            $updateData = [
                'NAME' => 'Updated Helper Field',
                'TYPE' => 'S'
            ];

            $updateResult = $this->fieldService->updateByCode(
                'lists',
                $this->testListCode,
                $fieldId,
                $updateData
            );

            $this->assertTrue($updateResult->isSuccess());

        } finally {
            // Test deleteByCode
            $deleteResult = $this->fieldService->deleteByCode(
                'lists',
                $this->testListCode,
                $fieldId
            );

            $this->assertTrue($deleteResult->isSuccess());
        }
    }
}
