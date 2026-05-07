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

namespace Bitrix24\SDK\Tests\Integration\Services\Lists\Section\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\AddedItemBatchResult;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Core\Result\UpdatedItemBatchResult;
use Bitrix24\SDK\Services\Lists\Section\Service\Batch;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

/**
 * Class BatchTest
 *
 * Integration tests for Section Batch service
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Lists\Section\Service
 */
#[CoversClass(Batch::class)]
#[CoversMethod(Batch::class, 'add')]
#[CoversMethod(Batch::class, 'update')]
#[CoversMethod(Batch::class, 'delete')]
#[CoversMethod(Batch::class, 'list')]
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
        $this->batchService = Factory::getServiceBuilder()->getListsScope()->section()->batch;

        // Create a test list for sections
        $this->testListCode = 'batch_section_list_' . (int)(microtime(true) * 1000000);
        $listFields = [
            'NAME' => 'Test List for Section Batch Integration',
            'DESCRIPTION' => 'Test list created for section batch tests',
            'SORT' => 100,
            'BIZPROC' => 'N'
        ];

        $addedItemResult = Factory::getServiceBuilder()->getListsScope()->lists()->add(
            'lists',
            $this->testListCode,
            $listFields
        );

        $this->testListId = $addedItemResult->getId();
    }

    /**
     * Clean up test environment
     */
    #[\Override]
    protected function tearDown(): void
    {
        try {
            // Delete test list
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
        $uniquePrefix = 'batch_test_' . (int)(microtime(true) * 1000000);

        $sectionsData = [
            [
                'IBLOCK_TYPE_ID' => 'lists',
                'IBLOCK_ID' => $this->testListId,
                'SECTION_CODE' => $uniquePrefix . '_1',
                'FIELDS' => [
                    'NAME' => 'Batch Test Section 1',
                    'DESCRIPTION' => 'First batch test section',
                    'SORT' => 100,
                    'ACTIVE' => 'Y'
                ]
            ],
            [
                'IBLOCK_TYPE_ID' => 'lists',
                'IBLOCK_ID' => $this->testListId,
                'SECTION_CODE' => $uniquePrefix . '_2',
                'FIELDS' => [
                    'NAME' => 'Batch Test Section 2',
                    'DESCRIPTION' => 'Second batch test section',
                    'SORT' => 200,
                    'ACTIVE' => 'Y'
                ]
            ],
            [
                'IBLOCK_TYPE_ID' => 'lists',
                'IBLOCK_ID' => $this->testListId,
                'SECTION_CODE' => $uniquePrefix . '_3',
                'FIELDS' => [
                    'NAME' => 'Batch Test Section 3',
                    'DESCRIPTION' => 'Third batch test section',
                    'SORT' => 300,
                    'ACTIVE' => 'Y'
                ]
            ]
        ];

        $createdSectionIds = [];

        try {
            $results = $this->batchService->add($sectionsData);
            $resultCount = 0;

            foreach ($results as $result) {
                $this->assertInstanceOf(AddedItemBatchResult::class, $result);
                $this->assertIsInt($result->getId());
                $this->assertGreaterThan(0, $result->getId());
                $createdSectionIds[] = $result->getId();
                $resultCount++;
            }

            $this->assertEquals(3, $resultCount);
            $this->assertCount(3, $createdSectionIds);

        } finally {
            // Clean up: delete created sections
            foreach ($createdSectionIds as $createdSectionId) {
                try {
                    Factory::getServiceBuilder()->getListsScope()->section()->delete(
                        'lists',
                        $this->testListId,
                        $createdSectionId
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
        $uniquePrefix = 'batch_update_' . (int)(microtime(true) * 1000000);

        // First, create sections to update
        $createdSectionIds = [];
        for ($i = 1; $i <= 3; $i++) {
            $addResult = Factory::getServiceBuilder()->getListsScope()->section()->add(
                'lists',
                $this->testListId,
                $uniquePrefix . '_' . $i,
                [
                    'NAME' => 'Section for Update ' . $i,
                    'SORT' => 100 * $i,
                    'ACTIVE' => 'Y'
                ]
            );
            $createdSectionIds[] = $addResult->getId();
        }

        try {
            // Prepare update data
            $updateData = [];
            foreach ($createdSectionIds as $index => $sectionId) {
                $updateData[$sectionId] = [
                    'IBLOCK_TYPE_ID' => 'lists',
                    'IBLOCK_ID' => $this->testListId,
                    'SECTION_ID' => $sectionId,
                    'FIELDS' => [
                        'NAME' => 'Updated Section ' . ($index + 1),
                        'DESCRIPTION' => 'Updated description ' . ($index + 1),
                        'SORT' => 500 + ($index * 10)
                    ]
                ];
            }

            // Test batch update
            $results = $this->batchService->update($updateData);
            $resultCount = 0;

            foreach ($results as $result) {
                $this->assertInstanceOf(UpdatedItemBatchResult::class, $result);
                $this->assertTrue($result->isSuccess());
                $resultCount++;
            }

            $this->assertEquals(3, $resultCount);

        } finally {
            // Clean up: delete created sections
            foreach ($createdSectionIds as $createdSectionId) {
                try {
                    Factory::getServiceBuilder()->getListsScope()->section()->delete(
                        'lists',
                        $this->testListId,
                        $createdSectionId
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
        $uniquePrefix = 'batch_delete_' . (int)(microtime(true) * 1000000);

        // First, create sections to delete
        $createdSectionIds = [];
        for ($i = 1; $i <= 3; $i++) {
            $addResult = Factory::getServiceBuilder()->getListsScope()->section()->add(
                'lists',
                $this->testListId,
                $uniquePrefix . '_' . $i,
                [
                    'NAME' => 'Section for Delete ' . $i,
                    'SORT' => 100 * $i,
                    'ACTIVE' => 'Y'
                ]
            );
            $createdSectionIds[] = $addResult->getId();
        }

        // Prepare delete data
        $deleteData = [];
        foreach ($createdSectionIds as $createdSectionId) {
            $deleteData[] = [
                'IBLOCK_TYPE_ID' => 'lists',
                'IBLOCK_ID' => $this->testListId,
                'SECTION_ID' => $createdSectionId
            ];
        }

        // Test batch delete
        $generator = $this->batchService->delete($deleteData);
        $resultCount = 0;

        foreach ($generator as $result) {
            $this->assertInstanceOf(DeletedItemBatchResult::class, $result);
            $this->assertTrue($result->isSuccess());
            $resultCount++;
        }

        $this->assertEquals(3, $resultCount);
    }
}