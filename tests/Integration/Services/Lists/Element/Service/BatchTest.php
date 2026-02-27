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

namespace Bitrix24\SDK\Tests\Integration\Services\Lists\Element\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\AddedItemBatchResult;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Core\Result\UpdatedItemBatchResult;
use Bitrix24\SDK\Services\Lists\Element\Result\ElementItemResult;
use Bitrix24\SDK\Services\Lists\Element\Service\Batch;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

/**
 * Class BatchTest
 *
 * Integration tests for Lists Element Batch service
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Lists\Element\Service
 */
#[CoversClass(Batch::class)]
#[CoversMethod(Batch::class, 'add')]
#[CoversMethod(Batch::class, 'update')]
#[CoversMethod(Batch::class, 'delete')]
#[CoversMethod(Batch::class, 'get')]
class BatchTest extends TestCase
{
    use CustomBitrix24Assertions;

    private Batch $batchService;

    private int $testListId;

    private string $testListCode;

    /**
     * @throws \Exception
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->batchService = Factory::getServiceBuilder()->getListsScope()->element()->batch;

        // Create a test list for element operations
        $this->testListCode = 'sdk_element_batch_' . (int)(microtime(true) * 1000000);
        $listFields = [
            'NAME' => 'SDK Element Batch Test List',
            'DESCRIPTION' => 'Test list for element batch integration tests',
            'SORT' => 100,
            'BIZPROC' => 'N'
        ];

        $listsService = Factory::getServiceBuilder()->getListsScope()->lists();
        $addedItemResult = $listsService->add('lists', $this->testListCode, $listFields);
        $this->testListId = $addedItemResult->getId();
    }

    /**
     * Clean up test environment
     */
    #[\Override]
    protected function tearDown(): void
    {
        // Clean up: delete the test list
        if (isset($this->testListId)) {
            try {
                $listsService = Factory::getServiceBuilder()->getListsScope()->lists();
                $listsService->delete('lists', $this->testListId);
            } catch (\Exception) {
                // Ignore cleanup errors
            }
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
        $uniquePrefix = 'batch_element_' . (int)(microtime(true) * 1000000);

        $elementsData = [
            [
                'IBLOCK_TYPE_ID' => 'lists',
                'IBLOCK_ID' => $this->testListId,
                'ELEMENT_CODE' => $uniquePrefix . '_1',
                'FIELDS' => [
                    'NAME' => 'Batch Test Element 1'
                ]
            ],
            [
                'IBLOCK_TYPE_ID' => 'lists',
                'IBLOCK_ID' => $this->testListId,
                'ELEMENT_CODE' => $uniquePrefix . '_2',
                'FIELDS' => [
                    'NAME' => 'Batch Test Element 2'
                ]
            ],
            [
                'IBLOCK_TYPE_ID' => 'lists',
                'IBLOCK_ID' => $this->testListId,
                'ELEMENT_CODE' => $uniquePrefix . '_3',
                'FIELDS' => [
                    'NAME' => 'Batch Test Element 3'
                ]
            ]
        ];

        $createdElementIds = [];

        try {
            $results = $this->batchService->add($elementsData);
            $resultCount = 0;

            foreach ($results as $result) {
                $this->assertInstanceOf(AddedItemBatchResult::class, $result);
                $this->assertIsInt($result->getId());
                $this->assertGreaterThan(0, $result->getId());

                $createdElementIds[] = $result->getId();
                $resultCount++;
            }

            $this->assertEquals(3, $resultCount);
            $this->assertCount(3, $createdElementIds);

        } finally {
            // Clean up: delete created elements
            foreach ($createdElementIds as $createdElementId) {
                try {
                    $elementService = Factory::getServiceBuilder()->getListsScope()->element();
                    $elementService->delete('lists', $this->testListId, $createdElementId);
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
        $uniquePrefix = 'batch_update_' . (int)(microtime(true) * 1000000);
        $createdElementIds = [];
        $elementService = Factory::getServiceBuilder()->getListsScope()->element();

        try {
            // First, create test elements
            for ($i = 1; $i <= 3; $i++) {
                $addResult = $elementService->add(
                    'lists',
                    $this->testListId,
                    $uniquePrefix . '_' . $i,
                    ['NAME' => 'Element to Update ' . $i]
                );
                $createdElementIds[] = $addResult->getId();
            }

            // Prepare batch update data
            $updateData = [];
            foreach ($createdElementIds as $index => $elementId) {
                $updateData[] = [
                    'IBLOCK_TYPE_ID' => 'lists',
                    'IBLOCK_ID' => $this->testListId,
                    'ELEMENT_ID' => $elementId,
                    'FIELDS' => [
                        'NAME' => 'Updated Element ' . ($index + 1)
                    ]
                ];
            }

            // Perform batch update
            $updateResults = $this->batchService->update($updateData);
            $resultCount = 0;

            foreach ($updateResults as $updateResult) {
                $this->assertInstanceOf(UpdatedItemBatchResult::class, $updateResult);
                $this->assertTrue($updateResult->isSuccess());
                $resultCount++;
            }

            $this->assertEquals(3, $resultCount);

            // Verify updates were successful
            for ($i = 0; $i < 3; $i++) {
                $getResult = $elementService->get(
                    'lists',
                    $this->testListId,
                    $createdElementIds[$i]
                );
                $elements = $getResult->getElements();
                $this->assertNotEmpty($elements);
                $this->assertEquals('Updated Element ' . ($i + 1), $elements[0]->NAME);
            }

        } finally {
            // Clean up: delete created elements
            foreach ($createdElementIds as $createdElementId) {
                try {
                    $elementService->delete('lists', $this->testListId, $createdElementId);
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
        $uniquePrefix = 'batch_delete_' . (int)(microtime(true) * 1000000);
        $createdElementIds = [];
        $elementService = Factory::getServiceBuilder()->getListsScope()->element();

        // First, create test elements
        for ($i = 1; $i <= 3; $i++) {
            $addResult = $elementService->add(
                'lists',
                $this->testListId,
                $uniquePrefix . '_' . $i,
                ['NAME' => 'Element to Delete ' . $i]
            );
            $createdElementIds[] = $addResult->getId();
        }

        // Prepare batch delete data
        $deleteData = [];
        foreach ($createdElementIds as $elementId) {
            $deleteData[] = [
                'IBLOCK_TYPE_ID' => 'lists',
                'IBLOCK_ID' => $this->testListId,
                'ELEMENT_ID' => $elementId
            ];
        }

        // Perform batch delete
        $generator = $this->batchService->delete($deleteData);
        $resultCount = 0;

        foreach ($generator as $result) {
            $this->assertInstanceOf(DeletedItemBatchResult::class, $result);
            $this->assertTrue($result->isSuccess());
            $resultCount++;
        }

        $this->assertEquals(3, $resultCount);

        // Verify elements were deleted - they should not be found anymore
        foreach ($createdElementIds as $createdElementId) {
            $getResult = $elementService->get(
                'lists',
                $this->testListId,
                $createdElementId
            );
            $elements = $getResult->getElements();
            $this->assertEmpty($elements, 'Element should be deleted but was found');
        }
    }

    /**
     * Test batch get operation with traversable list
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testBatchGet(): void
    {
        $uniquePrefix = 'batch_get_' . (int)(microtime(true) * 1000000);
        $createdElementIds = [];
        $elementService = Factory::getServiceBuilder()->getListsScope()->element();

        try {
            // First, create test elements (more than 50 to test pagination)
            for ($i = 1; $i <= 75; $i++) {
                $addResult = $elementService->add(
                    'lists',
                    $this->testListId,
                    $uniquePrefix . '_' . $i,
                    ['NAME' => 'Batch Get Element ' . $i]
                );
                $createdElementIds[] = $addResult->getId();
            }

            // Test batch get with all elements (no limit to test automatic pagination)
            $getAllResults = $this->batchService->get(
                'lists',
                $this->testListId,
                ['ID', 'NAME', 'CODE'], // select fields
                ['%NAME' => 'Batch Get Element'], // filter
                ['ID' => 'asc'], // order
                null // no limit to get all elements
            );

            $resultCount = 0;
            $foundElementIds = [];

            foreach ($getAllResults as $getAllResult) {
                $this->assertInstanceOf(ElementItemResult::class, $getAllResult);
                $this->assertStringContainsString('Batch Get Element', $getAllResult->NAME);

                if (in_array($getAllResult->ID, $createdElementIds)) {
                    $foundElementIds[] = $getAllResult->ID;
                }

                $resultCount++;
            }

            $this->assertGreaterThanOrEqual(75, $resultCount);
            $this->assertCount(75, $foundElementIds);

            // Test batch get with filter and limit (should respect pagination boundaries)
            $limitedResults = $this->batchService->get(
                'lists',
                $this->testListId,
                ['ID', 'NAME'],
                ['%NAME' => 'Batch Get Element'],
                ['ID' => 'desc'],
                60 // limit to 60 results (more than one API page but less than total)
            );

            $limitedCount = 0;
            foreach ($limitedResults as $limitedResult) {
                $this->assertInstanceOf(ElementItemResult::class, $limitedResult);
                $limitedCount++;

                // Count all results up to limit
                if ($limitedCount >= 60) {
                    break; // Stop after reaching our specified limit
                }
            }

            $this->assertGreaterThanOrEqual(60, $limitedCount);

            // Test with small limit to verify it works for single page
            $smallLimitResults = $this->batchService->get(
                'lists',
                $this->testListId,
                ['ID', 'NAME'],
                ['%NAME' => 'Batch Get Element'],
                ['ID' => 'asc'],
                25 // small limit within single API page
            );

            $smallLimitCount = 0;
            foreach ($smallLimitResults as $smallLimitResult) {
                $this->assertInstanceOf(ElementItemResult::class, $smallLimitResult);
                $smallLimitCount++;

                if ($smallLimitCount >= 25) {
                    break;
                }
            }

            $this->assertGreaterThanOrEqual(25, $smallLimitCount);

        } finally {
            // Clean up: delete created elements
            foreach ($createdElementIds as $createdElementId) {
                try {
                    $elementService->delete('lists', $this->testListId, $createdElementId);
                } catch (\Exception) {
                    // Ignore cleanup errors
                }
            }
        }
    }
}
