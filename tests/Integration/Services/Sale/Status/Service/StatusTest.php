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

namespace Bitrix24\SDK\Tests\Integration\Services\Sale\Status\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Sale\Status\Result\StatusItemResult;
use Bitrix24\SDK\Services\Sale\Status\Service\Status;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Class StatusTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Sale\Status\Service
 */
#[CoversMethod(Status::class, 'add')]
#[CoversMethod(Status::class, 'update')]
#[CoversMethod(Status::class, 'get')]
#[CoversMethod(Status::class, 'list')]
#[CoversMethod(Status::class, 'delete')]
#[CoversMethod(Status::class, 'getFields')]
#[CoversClass(Status::class)]
class StatusTest extends TestCase
{
    protected Status $statusService;

    protected array $testStatusIds = [];

    /**
     * @throws BaseException
     * @throws TransportException
     */
    protected function setUp(): void
    {
        $this->statusService = Fabric::getServiceBuilder()->getSaleScope()->status();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    protected function tearDown(): void
    {
        // Clean up any test statuses created during tests
        foreach ($this->testStatusIds as $testRectorPrefix202407StatusId) {
            try {
                $this->deleteStatus($testRectorPrefix202407StatusId);
            } catch (\Exception) {
                // Ignore errors during cleanup
            }
        }
    }

    /**
     * Helper method to create a test status
     *
     * @param string|null $statusId Custom status ID or null for auto-generated
     * @param string $name Status name
     * @param int $sort Sort order
     * @param bool $notify Whether to notify customer
     * @return string ID of the created status
     * @throws BaseException
     * @throws TransportException
     */
    protected function createStatus(?string $statusId = null, string $name = 'Test Status', int $sort = 500, bool $notify = false): string
    {
        // Generate a unique ID if not provided - must be max 2 characters
        $statusId ??= 'T' . chr(random_int(65, 90)); // Random letter A-Z with prefix T

        $this->statusService->add([
            'id' => $statusId,
            'type' => 'O', // Order status
            'sort' => $sort,
            'notify' => $notify,
            'name' => [
                'ru' => $name,
                'en' => $name . ' (EN)',
            ],
        ]);

        $this->testStatusIds[] = $statusId;
        return $statusId;
    }

    /**
     * Helper method to delete a status
     *
     * @param string $statusId ID of the status to delete
     * @throws BaseException
     * @throws TransportException
     */
    protected function deleteStatus(string $statusId): void
    {
        $this->statusService->delete($statusId);
        $this->testStatusIds = array_diff($this->testStatusIds, [$statusId]);
    }

    /**
     * Test adding a new status
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testAdd(): void
    {
        $statusId = 'T' . chr(random_int(65, 90)); // 2-character ID (max length allowed by API)
        $statusName = 'Test Status ' . time();
        $sortOrder = 500;
        $notify = false;

        // Create status
        $statusAddResult = $this->statusService->add([
            'id' => $statusId,
            'type' => 'O', // Order status
            'sort' => $sortOrder,
            'notify' => $notify,
            'name' => [
                'ru' => $statusName,
                'en' => $statusName . ' (EN)',
            ],
        ]);

        $this->testStatusIds[] = $statusId;

        // Verify result was successful
        $this->assertTrue($statusAddResult->isSuccess());

        // Verify status data
        $statusItemResult = $statusAddResult->getStatus();
        $this->assertInstanceOf(StatusItemResult::class, $statusItemResult);
        $this->assertEquals($statusId, $statusItemResult->id);
        $this->assertEquals('O', $statusItemResult->type);
        $this->assertEquals($sortOrder, $statusItemResult->sort);
    }

    /**
     * Test updating an existing status
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testUpdate(): void
    {
        // Create a status to update
        $statusId = $this->createStatus();
        $newName = 'Updated Status ' . time();
        $newSort = 600;

        // Update the status
        $statusUpdateResult = $this->statusService->update($statusId, [
            'type' => 'O', // Order status - обязательное поле
            'sort' => $newSort,
            'name' => [
                'ru' => $newName,
                'en' => $newName . ' (EN)',
            ],
        ]);

        // Verify update was successful
        $this->assertTrue($statusUpdateResult->isSuccess());

        // Get the updated status and verify changes
        $statusResult = $this->statusService->get($statusId);
        $statusItemResult = $statusResult->getStatus();

        $this->assertEquals($statusId, $statusItemResult->id);
        $this->assertEquals($newSort, $statusItemResult->sort);
        // Note: We would check for $newName but it depends on the selected language
    }

    /**
     * Test getting a status by ID
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testGet(): void
    {
        // Create a status to retrieve
        $statusId = $this->createStatus();

        // Get the status
        $statusResult = $this->statusService->get($statusId);

        // Verify result
        $statusItemResult = $statusResult->getStatus();
        $this->assertInstanceOf(StatusItemResult::class, $statusItemResult);
        $this->assertEquals($statusId, $statusItemResult->id);
        $this->assertEquals('O', $statusItemResult->type);
    }

    /**
     * Test listing statuses with filters and sorting
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testList(): void
    {
        // Create a status to ensure there's at least one
        $statusId = $this->createStatus();

        // Test listing all statuses
        $statusesResult = $this->statusService->list(['id', 'name', 'type'], [], ['sort' => 'asc']);
        $statuses = $statusesResult->getStatuses();
        $this->assertGreaterThan(0, count($statuses));

        // Test filtering by type
        $filteredResult = $this->statusService->list(['id', 'name'], ['type' => 'O'], ['sort' => 'asc']);
        $filteredStatuses = $filteredResult->getStatuses();
        $this->assertGreaterThan(0, count($filteredStatuses));

        // Test filtering by ID
        $byIdResult = $this->statusService->list(['id', 'name'], ['id' => $statusId], []);
        $idStatuses = $byIdResult->getStatuses();
        $this->assertEquals(1, count($idStatuses));
        $this->assertEquals($statusId, $idStatuses[0]->id);
    }

    /**
     * Test deleting a status
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelete(): void
    {
        // Create a status to delete
        $statusId = $this->createStatus();

        // Delete the status
        $deletedItemResult = $this->statusService->delete($statusId);

        // Verify deletion was successful
        $this->assertTrue($deletedItemResult->isSuccess());

        // Remove from tracked IDs since we've deleted it
        $this->testStatusIds = array_diff($this->testStatusIds, [$statusId]);

        // Try to get the deleted status, should throw an exception
        try {
            $this->statusService->get($statusId);
            $this->fail('Exception was not thrown when getting deleted status');
        } catch (BaseException) {
            // Expected exception - сообщение может отличаться от "not found"
            // Проверяем только что исключение было выброшено, сообщение не проверяем
            $this->assertTrue(true, 'Exception thrown as expected when accessing deleted status');
        }
    }

    /**
     * Test getting available fields for a status
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetFields(): void
    {
        $statusFieldsResult = $this->statusService->getFields();

        // Get fields using the proper method
        $fields = $statusFieldsResult->getFieldsDescription();

        // Проверяем только наличие массива полей
        $this->assertIsArray($fields);

        // Проверяем наличие обязательных полей
        $this->assertArrayHasKey('id', $fields);
        $this->assertArrayHasKey('type', $fields);
    }
}
