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
use Bitrix24\SDK\Services\Lists\Field\Result\AddedFieldBatchResult;
use Bitrix24\SDK\Core\Result\UpdatedItemBatchResult;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Services\Lists\Field\Service\Batch;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

/**
 * Class BatchTest
 *
 * Integration tests for Field Batch service
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Lists\Field\Service
 */
#[CoversClass(Batch::class)]
#[CoversMethod(Batch::class, 'add')]
#[CoversMethod(Batch::class, 'update')]
#[CoversMethod(Batch::class, 'delete')]
class BatchTest extends TestCase
{
    use CustomBitrix24Assertions;

    private Batch $batchService;

    private int $testListId;

    /**
     * Set up test environment
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->batchService = Factory::getServiceBuilder()->getListsScope()->field()->batch;

        // Create a test list for field operations
        $uniqueCode = 'test_field_batch_' . (int)(microtime(true) * 1000000);
        $listFields = [
            'NAME' => 'Test List for Field Batch Operations',
            'DESCRIPTION' => 'Test list created for field batch integration tests',
            'SORT' => 100,
            'BIZPROC' => 'N'
        ];

        $addedItemResult = Factory::getServiceBuilder()->getListsScope()->lists()->add(
            'lists',
            $uniqueCode,
            $listFields
        );

        $this->testListId = $addedItemResult->getId();
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
     * Test batch add operation
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testBatchAdd(): void
    {
        $uniquePrefix = 'batch_field_' . (int)(microtime(true) * 1000000);

        $fieldsData = [
            [
                'IBLOCK_TYPE_ID' => 'lists',
                'IBLOCK_ID' => $this->testListId,
                'FIELDS' => [
                    'NAME' => 'Batch Test Field 1',
                    'TYPE' => 'S',
                    'CODE' => $uniquePrefix . '_1',
                    'SORT' => 100
                ]
            ],
            [
                'IBLOCK_TYPE_ID' => 'lists',
                'IBLOCK_ID' => $this->testListId,
                'FIELDS' => [
                    'NAME' => 'Batch Test Field 2',
                    'TYPE' => 'N',
                    'CODE' => $uniquePrefix . '_2',
                    'SORT' => 200
                ]
            ],
            [
                'IBLOCK_TYPE_ID' => 'lists',
                'IBLOCK_ID' => $this->testListId,
                'FIELDS' => [
                    'NAME' => 'Batch Test List Field',
                    'TYPE' => 'L',
                    'CODE' => $uniquePrefix . '_3',
                    'SORT' => 300,
                    'LIST_TEXT_VALUES' => "Option 1\nOption 2\nOption 3"
                ]
            ]
        ];

        $fieldIds = [];

        try {
            // Test batch add
            $results = iterator_to_array($this->batchService->add($fieldsData));

            $this->assertCount(3, $results);

            foreach ($results as $result) {
                $this->assertInstanceOf(AddedFieldBatchResult::class, $result);
                $this->assertNotEmpty($result->getId());
                $this->assertStringStartsWith('PROPERTY_', $result->getId());

                $fieldIds[] = $result->getId();
            }

            // Verify fields were created by checking they exist
            $fieldService = Factory::getServiceBuilder()->getListsScope()->field();
            $allFieldsResult = $fieldService->get('lists', $this->testListId);
            $allFields = $allFieldsResult->fields();

            $createdFieldNames = array_map(fn($field) => $field->NAME, $allFields);
            $this->assertContains('Batch Test Field 1', $createdFieldNames);
            $this->assertContains('Batch Test Field 2', $createdFieldNames);
            $this->assertContains('Batch Test List Field', $createdFieldNames);

        } finally {
            // Clean up: delete all created fields
            foreach ($fieldIds as $fieldId) {
                try {
                    Factory::getServiceBuilder()->getListsScope()->field()->delete(
                        'lists',
                        $fieldId,
                        $this->testListId
                    );
                } catch (\Exception) {
                    // Ignore cleanup errors
                }
            }
        }
    }

    /**
     * Test batch update operation
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testBatchUpdate(): void
    {
        $uniquePrefix = 'batch_update_field_' . (int)(microtime(true) * 1000000);
        $fieldIds = [];

        try {
            // First create fields to update
            $fieldService = Factory::getServiceBuilder()->getListsScope()->field();

            for ($i = 1; $i <= 2; $i++) {
                $fieldData = [
                    'NAME' => 'Field to Update ' . $i,
                    'TYPE' => 'S',
                    'CODE' => $uniquePrefix . '_' . $i,
                    'SORT' => 100 + $i
                ];

                $addResult = $fieldService->add(
                    'lists',
                    $fieldData,
                    $this->testListId
                );

                $fieldIds[] = $addResult->getId();
            }

            // Prepare batch update data
            $updateData = [
                [
                    'IBLOCK_TYPE_ID' => 'lists',
                    'IBLOCK_ID' => $this->testListId,
                    'FIELD_ID' => $fieldIds[0],
                    'FIELDS' => [
                        'NAME' => 'Updated Batch Field 1',
                        'TYPE' => 'S',
                        'SORT' => 500
                    ]
                ],
                [
                    'IBLOCK_TYPE_ID' => 'lists',
                    'IBLOCK_ID' => $this->testListId,
                    'FIELD_ID' => $fieldIds[1],
                    'FIELDS' => [
                        'NAME' => 'Updated Batch Field 2',
                        'TYPE' => 'S',
                        'SORT' => 600
                    ]
                ]
            ];

            // Test batch update
            $results = iterator_to_array($this->batchService->update($updateData));

            $this->assertCount(2, $results);

            foreach ($results as $result) {
                $this->assertInstanceOf(UpdatedItemBatchResult::class, $result);
                $this->assertTrue($result->isSuccess());
            }

            // Verify updates were successful
            $allFieldsResult = $fieldService->get('lists', $this->testListId);
            $allFields = $allFieldsResult->fields();

            $updatedFieldNames = array_map(fn($field) => $field->NAME, $allFields);
            $this->assertContains('Updated Batch Field 1', $updatedFieldNames);
            $this->assertContains('Updated Batch Field 2', $updatedFieldNames);

        } finally {
            // Clean up: delete all test fields
            foreach ($fieldIds as $fieldId) {
                try {
                    Factory::getServiceBuilder()->getListsScope()->field()->delete(
                        'lists',
                        $fieldId,
                        $this->testListId
                    );
                } catch (\Exception) {
                    // Ignore cleanup errors
                }
            }
        }
    }

    /**
     * Test batch delete operation
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testBatchDelete(): void
    {
        $uniquePrefix = 'batch_delete_field_' . (int)(microtime(true) * 1000000);
        $fieldIds = [];

        try {
            // First create fields to delete
            $fieldService = Factory::getServiceBuilder()->getListsScope()->field();

            for ($i = 1; $i <= 3; $i++) {
                $fieldData = [
                    'NAME' => 'Field to Delete ' . $i,
                    'TYPE' => 'S',
                    'CODE' => $uniquePrefix . '_' . $i,
                    'SORT' => 100 + $i
                ];

                $addResult = $fieldService->add(
                    'lists',
                    $fieldData,
                    $this->testListId
                );

                $fieldIds[] = $addResult->getId();
            }

            // Prepare batch delete data
            $deleteData = [];
            foreach ($fieldIds as $fieldId) {
                $deleteData[] = [
                    'IBLOCK_TYPE_ID' => 'lists',
                    'IBLOCK_ID' => $this->testListId,
                    'FIELD_ID' => $fieldId
                ];
            }

            // Test batch delete
            $results = iterator_to_array($this->batchService->delete($deleteData));

            $this->assertCount(3, $results);

            foreach ($results as $result) {
                $this->assertInstanceOf(DeletedItemBatchResult::class, $result);
                $this->assertTrue($result->isSuccess());
            }

            // Clear fieldIds as they were successfully deleted
            $fieldIds = [];

            // Verify fields were deleted
            $allFieldsResult = $fieldService->get('lists', $this->testListId);
            $allFields = $allFieldsResult->fields();

            $remainingFieldNames = array_map(fn($field) => $field->NAME, $allFields);
            $this->assertNotContains('Field to Delete 1', $remainingFieldNames);
            $this->assertNotContains('Field to Delete 2', $remainingFieldNames);
            $this->assertNotContains('Field to Delete 3', $remainingFieldNames);

        } finally {
            // Clean up: delete any remaining test fields
            foreach ($fieldIds as $fieldId) {
                try {
                    Factory::getServiceBuilder()->getListsScope()->field()->delete(
                        'lists',
                        $fieldId,
                        $this->testListId
                    );
                } catch (\Exception) {
                    // Ignore cleanup errors
                }
            }
        }
    }
}
