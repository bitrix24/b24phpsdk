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

namespace Bitrix24\SDK\Tests\Integration\Services\Calendar\Resource\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Calendar\Resource\Service\Resource;
use Bitrix24\SDK\Services\Calendar\Resource\Result\ResourceItemResult;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use Bitrix24\SDK\Services\ServiceBuilder;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

/**
 * Class ResourceTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Calendar\Resource\Service
 */
#[CoversMethod(Resource::class, 'add')]
#[CoversMethod(Resource::class, 'update')]
#[CoversMethod(Resource::class, 'list')]
#[CoversMethod(Resource::class, 'bookingList')]
#[CoversMethod(Resource::class, 'delete')]
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\Calendar\Resource\Service\Resource::class)]
class ResourceTest extends TestCase
{
    use CustomBitrix24Assertions;

    protected Resource $resourceService;

    protected ServiceBuilder $serviceBuilder;

    protected array $createdResourceIds = [];

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->serviceBuilder = Factory::getServiceBuilder();
        $this->resourceService = $this->serviceBuilder->getCalendarScope()->resource();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function tearDown(): void
    {
        // Clean up created resources
        foreach ($this->createdResourceIds as $createdResourceId) {
            try {
                $this->resourceService->delete($createdResourceId);
            } catch (\Exception) {
                // Ignore cleanup errors
            }
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAdd(): void
    {
        $resourceName = 'Test Resource ' . uniqid();
        
        $addedItemResult = $this->resourceService->add($resourceName);

        $this->assertIsNumeric($addedItemResult->getId());
        $this->assertGreaterThan(0, $addedItemResult->getId());
        
        // Track for cleanup
        $this->createdResourceIds[] = $addedItemResult->getId();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testUpdate(): void
    {
        // Create a resource first
        $originalName = 'Original Resource ' . uniqid();
        $addedItemResult = $this->resourceService->add($originalName);
        $resourceId = $addedItemResult->getId();
        $this->createdResourceIds[] = $resourceId;

        // Update the resource
        $updatedName = 'Updated Resource ' . uniqid();
        $updateResult = $this->resourceService->update($resourceId, $updatedName);

        $this->assertEquals($resourceId, $updateResult->getId());

        // Verify the update by listing resources and finding our resource
        $resourcesResult = $this->resourceService->list();
        $resources = $resourcesResult->getResources();
        
        $foundResource = null;
        foreach ($resources as $resource) {
            if ((int)$resource->ID === $resourceId) {
                $foundResource = $resource;
                break;
            }
        }

        $this->assertNotNull($foundResource, 'Updated resource not found in list');
        $this->assertEquals($updatedName, $foundResource->NAME);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testList(): void
    {
        // Create a test resource
        $resourceName = 'List Test Resource ' . uniqid();
        $addedItemResult = $this->resourceService->add($resourceName);
        $resourceId = $addedItemResult->getId();
        $this->createdResourceIds[] = $resourceId;

        $resourcesResult = $this->resourceService->list();
        $resources = $resourcesResult->getResources();

        $this->assertIsArray($resources);
        $this->assertNotEmpty($resources);

        // Find our created resource
        $foundResource = null;
        foreach ($resources as $resource) {
            $this->assertInstanceOf(ResourceItemResult::class, $resource);
            $this->assertIsString($resource->ID);
            $this->assertIsString($resource->NAME);
            $this->assertIsString($resource->CREATED_BY);

            if ((int)$resource->ID === $resourceId) {
                $foundResource = $resource;
            }
        }

        $this->assertNotNull($foundResource, 'Created resource not found in list');
        $this->assertEquals($resourceName, $foundResource->NAME);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testBookingListWithResourceTypeIdList(): void
    {
        // Create a test resource
        $resourceName = 'Booking Test Resource ' . uniqid();
        $addedItemResult = $this->resourceService->add($resourceName);
        $resourceId = $addedItemResult->getId();
        $this->createdResourceIds[] = $resourceId;

        // Test booking list with resourceTypeIdList filter
        $filter = [
            'resourceTypeIdList' => [$resourceId],
            'from' => date('Y-m-d', strtotime('-30 days')),
            'to' => date('Y-m-d', strtotime('+30 days'))
        ];

        $bookingsResult = $this->resourceService->bookingList($filter);
        $bookings = $bookingsResult->getBookings();

        $this->assertIsArray($bookings);

        // Check structure of booking results if any exist
        foreach ($bookings as $booking) {
            $this->assertInstanceOf(ResourceItemResult::class, $booking);
            $this->assertIsString($booking->ID);
            $this->assertIsString($booking->NAME);
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelete(): void
    {
        // Create a resource first
        $resourceName = 'Delete Test Resource ' . uniqid();
        $addedItemResult = $this->resourceService->add($resourceName);
        $resourceId = $addedItemResult->getId();

        // Delete the resource
        $deletedItemResult = $this->resourceService->delete($resourceId);
        $this->assertTrue($deletedItemResult->isSuccess());

        // Verify deletion by checking if resource is no longer in list
        $resourcesResult = $this->resourceService->list();
        $resources = $resourcesResult->getResources();
        
        $foundResource = false;
        foreach ($resources as $resource) {
            if ((int)$resource->ID === $resourceId) {
                $foundResource = true;
                break;
            }
        }

        $this->assertFalse($foundResource, 'Deleted resource still found in list');
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAddUpdateDeleteFlow(): void
    {
        // Test complete CRUD flow
        $originalName = 'CRUD Flow Resource ' . uniqid();

        // Create
        $addedItemResult = $this->resourceService->add($originalName);
        $resourceId = $addedItemResult->getId();
        $this->assertGreaterThan(0, $resourceId);

        // Update
        $updatedName = 'Updated CRUD Resource ' . uniqid();
        $updateResult = $this->resourceService->update($resourceId, $updatedName);
        $this->assertEquals($resourceId, $updateResult->getId());

        // Verify in list
        $resourcesResult = $this->resourceService->list();
        $resources = $resourcesResult->getResources();
        $found = false;
        foreach ($resources as $resource) {
            if ((int)$resource->ID === $resourceId) {
                $this->assertEquals($updatedName, $resource->NAME);
                $found = true;
                break;
            }
        }

        $this->assertTrue($found);

        // Delete
        $deletedItemResult = $this->resourceService->delete($resourceId);
        $this->assertTrue($deletedItemResult->isSuccess());

        // Verify deletion
        $listResultAfterDelete = $this->resourceService->list();
        $resourcesAfterDelete = $listResultAfterDelete->getResources();
        $foundAfterDelete = false;
        foreach ($resourcesAfterDelete as $resourceAfterDelete) {
            if ((int)$resourceAfterDelete->ID === $resourceId) {
                $foundAfterDelete = true;
                break;
            }
        }

        $this->assertFalse($foundAfterDelete);
    }
}