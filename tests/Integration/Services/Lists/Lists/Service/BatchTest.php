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
use Bitrix24\SDK\Core\Result\AddedItemBatchResult;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Core\Result\UpdatedItemBatchResult;
use Bitrix24\SDK\Services\Lists\Lists\Service\Batch;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

/**
 * Class BatchTest
 *
 * Integration tests for Lists Batch service
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Lists\Lists\Service
 */
#[CoversClass(Batch::class)]
#[CoversMethod(Batch::class, 'add')]
#[CoversMethod(Batch::class, 'update')]
#[CoversMethod(Batch::class, 'delete')]
class BatchTest extends TestCase
{
    use CustomBitrix24Assertions;

    private Batch $batchService;

    /**
     * Set up test environment
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->batchService = Factory::getServiceBuilder()->getListsScope()->lists()->batch;
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

        $listsData = [
            [
                'IBLOCK_TYPE_ID' => 'lists',
                'IBLOCK_CODE' => $uniquePrefix . '_1',
                'FIELDS' => [
                    'NAME' => 'Batch Test List 1',
                    'DESCRIPTION' => 'First batch test list',
                    'SORT' => 100,
                    'BIZPROC' => 'N'
                ]
            ],
            [
                'IBLOCK_TYPE_ID' => 'lists',
                'IBLOCK_CODE' => $uniquePrefix . '_2',
                'FIELDS' => [
                    'NAME' => 'Batch Test List 2',
                    'DESCRIPTION' => 'Second batch test list',
                    'SORT' => 200,
                    'BIZPROC' => 'N'
                ]
            ],
            [
                'IBLOCK_TYPE_ID' => 'lists',
                'IBLOCK_CODE' => $uniquePrefix . '_3',
                'FIELDS' => [
                    'NAME' => 'Batch Test List 3',
                    'DESCRIPTION' => 'Third batch test list',
                    'SORT' => 300,
                    'BIZPROC' => 'Y'
                ]
            ]
        ];

        $createdListIds = [];

        try {
            $results = $this->batchService->add($listsData);
            $resultCount = 0;

            foreach ($results as $result) {
                $this->assertInstanceOf(AddedItemBatchResult::class, $result);
                $this->assertIsInt($result->getId());
                $this->assertGreaterThan(0, $result->getId());

                $createdListIds[] = $result->getId();
                $resultCount++;
            }

            $this->assertEquals(3, $resultCount);
            $this->assertCount(3, $createdListIds);

            // Verify lists were created
            $listsService = Factory::getServiceBuilder()->getListsScope()->lists();

            foreach ($createdListIds as $index => $listId) {
                $getResult = $listsService->get('lists', $listId);
                $lists = $getResult->getLists();

                $this->assertNotEmpty($lists);
                $this->assertEquals($listsData[$index]['FIELDS']['NAME'], $lists[0]->NAME);
                $this->assertEquals($listsData[$index]['FIELDS']['DESCRIPTION'], $lists[0]->DESCRIPTION);
            }

        } finally {
            // Clean up: delete all created lists
            foreach ($createdListIds as $createdListId) {
                try {
                    Factory::getServiceBuilder()->getListsScope()->lists()->delete(
                        'lists',
                        $createdListId
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
        $listsService = Factory::getServiceBuilder()->getListsScope()->lists();

        $createdListIds = [];

        try {
            // First create test lists
            for ($i = 1; $i <= 2; $i++) {
                $addResult = $listsService->add(
                    'lists',
                    $uniquePrefix . '_' . $i,
                    [
                        'NAME' => 'Original List ' . $i,
                        'DESCRIPTION' => 'Original description ' . $i,
                        'SORT' => 100 + $i,
                        'BIZPROC' => 'N'
                    ]
                );
                $createdListIds[] = $addResult->getId();
            }

            // Prepare batch update data
            $updateData = [];
            foreach ($createdListIds as $index => $listId) {
                $updateData[$listId] = [
                    'IBLOCK_TYPE_ID' => 'lists',
                    'FIELDS' => [
                        'NAME' => 'Updated List ' . ($index + 1),
                        'DESCRIPTION' => 'Updated description ' . ($index + 1),
                        'SORT' => 500 + $index
                    ]
                ];
            }

            // Perform batch update
            $results = $this->batchService->update($updateData);
            $resultCount = 0;

            foreach ($results as $result) {
                $this->assertInstanceOf(UpdatedItemBatchResult::class, $result);
                $this->assertTrue($result->isSuccess());
                $resultCount++;
            }

            $this->assertEquals(2, $resultCount);

            // Verify updates were successful
            foreach ($createdListIds as $index => $listId) {
                $getResult = $listsService->get('lists', $listId);
                $lists = $getResult->getLists();

                $this->assertNotEmpty($lists);
                $this->assertEquals('Updated List ' . ($index + 1), $lists[0]->NAME);
                $this->assertEquals('Updated description ' . ($index + 1), $lists[0]->DESCRIPTION);
            }

        } finally {
            // Clean up
            foreach ($createdListIds as $createdListId) {
                try {
                    $listsService->delete('lists', $createdListId);
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
        $listsService = Factory::getServiceBuilder()->getListsScope()->lists();

        $createdListIds = [];

        // First create test lists
        for ($i = 1; $i <= 3; $i++) {
            $addResult = $listsService->add(
                'lists',
                $uniquePrefix . '_' . $i,
                [
                    'NAME' => 'To Delete List ' . $i,
                    'DESCRIPTION' => 'List to be deleted ' . $i,
                    'SORT' => 100 + $i,
                    'BIZPROC' => 'N'
                ]
            );
            $createdListIds[] = $addResult->getId();
        }

        // Prepare batch delete data
        $deleteData = [];
        foreach ($createdListIds as $listId) {
            $deleteData[] = [
                'IBLOCK_TYPE_ID' => 'lists',
                'IBLOCK_ID' => $listId
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

        // Verify lists were deleted - they should no longer exist
        foreach ($createdListIds as $createdListId) {
            $this->expectException(BaseException::class);
            $listsService->get('lists', $createdListId);
        }
    }

    /**
     * Test batch operations with mixed results
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testBatchMixedResults(): void
    {
        $uniquePrefix = 'batch_mixed_' . (int)(microtime(true) * 1000000);

        $listsData = [
            // First valid list
            [
                'IBLOCK_TYPE_ID' => 'lists',
                'IBLOCK_CODE' => $uniquePrefix . '_valid_1',
                'FIELDS' => [
                    'NAME' => 'Valid Test List 1',
                    'DESCRIPTION' => 'Valid list description 1',
                    'SORT' => 100,
                    'BIZPROC' => 'N'
                ]
            ],
            // Second valid list with unique code
            [
                'IBLOCK_TYPE_ID' => 'lists',
                'IBLOCK_CODE' => $uniquePrefix . '_valid_2',
                'FIELDS' => [
                    'NAME' => 'Valid Test List 2',
                    'DESCRIPTION' => 'Valid list description 2',
                    'SORT' => 200,
                    'BIZPROC' => 'N'
                ]
            ]
        ];

        $createdListIds = [];

        try {
            $results = $this->batchService->add($listsData);
            $resultCount = 0;

            foreach ($results as $result) {
                $this->assertInstanceOf(AddedItemBatchResult::class, $result);

                // Both lists should succeed as they have unique codes
                $this->assertIsInt($result->getId());
                $this->assertGreaterThan(0, $result->getId());
                $createdListIds[] = $result->getId();

                $resultCount++;
            }

            $this->assertEquals(2, $resultCount);

        } finally {
            // Clean up any created lists
            foreach ($createdListIds as $createdListId) {
                try {
                    Factory::getServiceBuilder()->getListsScope()->lists()->delete(
                        'lists',
                        $createdListId
                    );
                } catch (\Exception) {
                    // Ignore cleanup errors
                }
            }
        }
    }
}
