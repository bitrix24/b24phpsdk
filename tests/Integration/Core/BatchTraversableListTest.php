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

namespace Bitrix24\SDK\Tests\Integration\Core;

use Bitrix24\SDK\Core\Batch;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Services\ServiceBuilder;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

#[CoversClass(Batch::class)]
class BatchTraversableListTest extends TestCase
{
    private Batch $batch;
    private ServiceBuilder $serviceBuilder;
    private array $createdContactIds;

    #[TestDox('test get contacts without sorting in batch mode with less than one page')]
    public function testSingleBatchWithoutSortingLess(): void
    {
        $greaterThanDefaultPageSize = 45;
        $originatorId = Uuid::v7()->toRfc4122();
        // add contacts
        $contacts = [];
        for ($i = 0; $i < $greaterThanDefaultPageSize; $i++) {
            $contacts[] = [
                'fields' => [
                    'NAME' => 'name-' . $i,
                    'ORIGINATOR_ID' => $originatorId
                ]
            ];
        }
        $itemCount = 0;
        $addedIds = [];
        foreach ($this->batch->addEntityItems('crm.contact.add', $contacts) as $addedContactResult) {
            $this->createdContactIds[] = $addedContactResult->getResult()[0];
            $addedIds[] = $addedContactResult->getResult()[0];
            $itemCount++;
        }
        $this->assertEquals(count($contacts), $itemCount);

        $foundIds = [];
        foreach ($this->batch->getTraversableList('crm.contact.list',
            [],
            [
                'ORIGINATOR_ID' => $originatorId
            ],
            [
                'ID',
                'NAME',
                'ORIGINATOR_ID'
            ]
        ) as $index => $itemContact) {
            $foundIds[] = $itemContact['ID'];
        }
        
        // Verify all contacts were found
        foreach ($addedIds as $addedItemId) {
            self::assertTrue(in_array($addedItemId, $foundIds));
        }
        
        // Verify the count of returned results
        $this->assertEquals(count($addedIds), count($foundIds), "All contacts should be returned");
    }
    
    #[TestDox('test get contacts without sorting in batch mode with more than one page')]
    public function testSingleBatchWithoutSorting(): void
    {
        $greaterThanDefaultPageSize = 120;
        $originatorId = Uuid::v7()->toRfc4122();
        // add contacts
        $contacts = [];
        for ($i = 0; $i < $greaterThanDefaultPageSize; $i++) {
            $contacts[] = [
                'fields' => [
                    'NAME' => 'name-' . $i,
                    'ORIGINATOR_ID' => $originatorId
                ]
            ];
        }
        $itemCount = 0;
        $addedIds = [];
        foreach ($this->batch->addEntityItems('crm.contact.add', $contacts) as $addedContactResult) {
            $this->createdContactIds[] = $addedContactResult->getResult()[0];
            $addedIds[] = $addedContactResult->getResult()[0];
            $itemCount++;
        }
        $this->assertEquals(count($contacts), $itemCount);

        $foundIds = [];
        foreach ($this->batch->getTraversableList('crm.contact.list',
            [],
            [
                'ORIGINATOR_ID' => $originatorId
            ],
            [
                'ID',
                'NAME',
                'ORIGINATOR_ID'
            ]
        ) as $index => $itemContact) {
            $foundIds[] = $itemContact['ID'];
        }
        
        // Verify all contacts were found
        foreach ($addedIds as $addedItemId) {
            self::assertTrue(in_array($addedItemId, $foundIds));
        }
        
        // Verify the count of returned results
        $this->assertEquals(count($addedIds), count($foundIds), "All contacts should be returned");
    }

    #[TestDox('test get contacts with DESC sorting in batch mode with more than one page')]
    public function testSingleBatchWithDescSorting(): void
    {
        $greaterThanDefaultPageSize = 120;
        $originatorId = Uuid::v7()->toRfc4122();
        // add contacts
        $contacts = [];
        for ($i = 0; $i < $greaterThanDefaultPageSize; $i++) {
            $contacts[] = [
                'fields' => [
                    'NAME' => 'name-' . $i,
                    'ORIGINATOR_ID' => $originatorId
                ]
            ];
        }
        $itemCount = 0;
        $addedIds = [];
        foreach ($this->batch->addEntityItems('crm.contact.add', $contacts) as $addedContactResult) {
            $this->createdContactIds[] = $addedContactResult->getResult()[0];
            $addedIds[] = $addedContactResult->getResult()[0];
            $itemCount++;
        }
        $this->assertEquals(count($contacts), $itemCount);

        $foundIds = [];
        foreach ($this->batch->getTraversableList('crm.contact.list',
            ['ID' => 'DESC'],
            [
                'ORIGINATOR_ID' => $originatorId
            ],
            [
                'ID',
                'NAME',
                'ORIGINATOR_ID'
            ]
        ) as $index => $itemContact) {
            $foundIds[] = $itemContact['ID'];
        }
        
        // Verify all contacts were found
        foreach ($addedIds as $addedItemId) {
            self::assertTrue(in_array($addedItemId, $foundIds));
        }
        
        // Verify the count of returned results
        $this->assertEquals(count($addedIds), count($foundIds), "All contacts should be returned");
    }
    
    #[TestDox('test get contacts with other sorting in batch mode with more than one page')]
    public function testSingleBatchWithOtherSorting(): void
    {
        $greaterThanDefaultPageSize = 120;
        $originatorId = Uuid::v7()->toRfc4122();
        // add contacts
        $contacts = [];
        for ($i = 0; $i < $greaterThanDefaultPageSize; $i++) {
            $contacts[] = [
                'fields' => [
                    'NAME' => 'name-' . $i,
                    'ORIGINATOR_ID' => $originatorId
                ]
            ];
        }
        $itemCount = 0;
        $addedIds = [];
        foreach ($this->batch->addEntityItems('crm.contact.add', $contacts) as $addedContactResult) {
            $this->createdContactIds[] = $addedContactResult->getResult()[0];
            $addedIds[] = $addedContactResult->getResult()[0];
            $itemCount++;
        }
        $this->assertEquals(count($contacts), $itemCount);

        $foundIds = [];
        foreach ($this->batch->getTraversableList('crm.contact.list',
            ['NAME' => 'DESC'],
            [
                'ORIGINATOR_ID' => $originatorId
            ],
            [
                'ID',
                'NAME',
                'ORIGINATOR_ID'
            ]
        ) as $index => $itemContact) {
            $foundIds[] = $itemContact['ID'];
        }
        
        // Verify all contacts were found
        foreach ($addedIds as $addedItemId) {
            self::assertTrue(in_array($addedItemId, $foundIds));
        }
        
        // Verify the count of returned results
        $this->assertEquals(count($addedIds), count($foundIds), "All contacts should be returned");
    }
    
    #[TestDox('test get contacts with other sorting in batch mode with more than one page and limit')]
    public function testSingleBatchWithOtherSortingAndLimit(): void
    {
        $greaterThanDefaultPageSize = 120;
        $originatorId = Uuid::v7()->toRfc4122();
        // add contacts
        $contacts = [];
        for ($i = 0; $i < $greaterThanDefaultPageSize; $i++) {
            $contacts[] = [
                'fields' => [
                    'NAME' => 'name-' . $i,
                    'ORIGINATOR_ID' => $originatorId
                ]
            ];
        }
        $itemCount = 0;
        $addedIds = [];
        foreach ($this->batch->addEntityItems('crm.contact.add', $contacts) as $addedContactResult) {
            $this->createdContactIds[] = $addedContactResult->getResult()[0];
            $addedIds[] = $addedContactResult->getResult()[0];
            $itemCount++;
        }
        $this->assertEquals(count($contacts), $itemCount);

        $foundIds = [];
        $limit = 90;
        foreach ($this->batch->getTraversableList('crm.contact.list',
            ['NAME' => 'DESC'],
            [
                'ORIGINATOR_ID' => $originatorId
            ],
            [
                'ID',
                'NAME',
                'ORIGINATOR_ID'
            ],
            $limit
        ) as $index => $itemContact) {
            $foundIds[] = $itemContact['ID'];
        }
        
        // Verify all contacts were found
        foreach ($foundIds as $foundItemId) {
            self::assertTrue(in_array($foundItemId, $addedIds));
        }
        
        // Verify the count of returned results
        $this->assertEquals($limit, count($foundIds), "All contacts should be returned");
    }
    
    #[TestDox('test get contacts by item-method with id sorting in batch mode with more than one page')]
    public function testSingleBatchWithItemIdSorting(): void
    {
        $greaterThanDefaultPageSize = 120;
        $originatorId = Uuid::v7()->toRfc4122();
        // add contacts
        $contacts = [];
        for ($i = 0; $i < $greaterThanDefaultPageSize; $i++) {
            $contacts[] = [
                'fields' => [
                    'NAME' => 'name-' . $i,
                    'ORIGINATOR_ID' => $originatorId
                ]
            ];
        }
        $itemCount = 0;
        $addedIds = [];
        foreach ($this->batch->addEntityItems('crm.contact.add', $contacts) as $addedContactResult) {
            $this->createdContactIds[] = $addedContactResult->getResult()[0];
            $addedIds[] = $addedContactResult->getResult()[0];
            $itemCount++;
        }
        $this->assertEquals(count($contacts), $itemCount);

        $foundIds = [];
        $entityTypeId = 3;
        foreach ($this->batch->getTraversableList('crm.item.list',
            ['id' => 'asc'],
            [
                'ORIGINATOR_ID' => $originatorId
            ],
            [
                'ID',
                'NAME',
                'ORIGINATOR_ID'
            ],
            null,
            ['entityTypeId' => $entityTypeId]
        ) as $index => $itemContact) {
            $foundIds[] = $itemContact['id'];
        }
        
        // Verify all contacts were found
        foreach ($addedIds as $addedItemId) {
            self::assertTrue(in_array($addedItemId, $foundIds));
        }
        
        // Verify the count of returned results
        $this->assertEquals(count($addedIds), count($foundIds), "All contacts should be returned");
    }
    
    #[TestDox('test get contacts with id-desc sorting in batch mode with more than 50 page')]
    public function testSingleBatchWithDescSortingMore(): void
    {
        $greaterThanDefaultPageSize = 2510;
        $originatorId = Uuid::v7()->toRfc4122();
        // add contacts
        $contacts = [];
        for ($i = 0; $i < $greaterThanDefaultPageSize; $i++) {
            $contacts[] = [
                'fields' => [
                    'NAME' => 'name-' . $i,
                    'ORIGINATOR_ID' => $originatorId
                ]
            ];
        }
        $itemCount = 0;
        $addedIds = [];
        foreach ($this->batch->addEntityItems('crm.contact.add', $contacts) as $addedContactResult) {
            $this->createdContactIds[] = $addedContactResult->getResult()[0];
            $addedIds[] = $addedContactResult->getResult()[0];
            $itemCount++;
        }
        $this->assertEquals(count($contacts), $itemCount);

        $foundIds = [];
        foreach ($this->batch->getTraversableList('crm.contact.list',
            ['ID' => 'DESC'],
            [
                'ORIGINATOR_ID' => $originatorId
            ],
            [
                'ID',
                'NAME',
                'ORIGINATOR_ID'
            ]
        ) as $index => $itemContact) {
            $foundIds[] = $itemContact['ID'];
        }
        
        // Verify all contacts were found
        foreach ($addedIds as $addedItemId) {
            self::assertTrue(in_array($addedItemId, $foundIds));
        }
        
        // Verify the count of returned results
        $this->assertEquals(count($addedIds), count($foundIds), "All contacts should be returned");
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setUp(): void
    {
        $this->batch = Factory::getBatchService();
        $this->serviceBuilder = Factory::getServiceBuilder();
    }

    public function tearDown(): void
    {
        if ($this->createdContactIds !== null) {
            foreach ($this->batch->deleteEntityItems('crm.contact.delete', $this->createdContactIds) as $result) {
            }
        }
    }
}
