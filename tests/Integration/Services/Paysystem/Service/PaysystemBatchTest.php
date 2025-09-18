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

namespace Bitrix24\SDK\Tests\Integration\Services\Paysystem\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Paysystem\Service\Paysystem;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Class PaysystemBatchTest
 *
 * Integration tests for Payment System batch operations
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Paysystem\Service
 */
#[CoversClass(\Bitrix24\SDK\Services\Paysystem\Service\Batch::class)]
class PaysystemBatchTest extends TestCase
{
    private const TEST_SEGMENT_ELEMENTS_COUNT = 50;

    protected Paysystem $paysystemService;

    /**
     * Get or create a person type ID for tests
     */
    private function getPersonTypeId(): int
    {
        $personTypeService = Fabric::getServiceBuilder()->getSaleScope()->personType();
        $personTypes = $personTypeService->list();
        
        if (count($personTypes->getPersonTypes()) > 0) {
            return $personTypes->getPersonTypes()[0]->id;
        }
        
        $personTypeResult = $personTypeService->add(['name' => 'Test Person Type ' . time()]);
        return $personTypeResult->getId();
    }

    /**
     * Create a test handler for payment system
     */
    private function createTestHandler(): string
    {
        $handlerService = Fabric::getServiceBuilder()->getPaysystemScope()->handler();
        $handlerName = 'Test Handler ' . time();
        $handlerCode = 'test_handler_' . time();
        $handlerSettings = [
            'CURRENCY' => ['USD'],
            'CLIENT_TYPE' => 'b2c',
            'FORM_DATA' => [
                'ACTION_URI' => 'https://example.com/payment_form.php',
                'METHOD' => 'POST',
                'FIELDS' => [
                    'paymentId' => [
                        'CODE' => 'PAYMENT_ID',
                        'VISIBLE' => 'Y'
                    ]
                ]
            ],
            'CODES' => [
                'PAYMENT_ID' => [
                    'NAME' => 'Payment ID',
                    'DESCRIPTION' => 'Payment identifier',
                    'SORT' => '100',
                    'GROUP' => 'PAYMENT',
                    'DEFAULT' => [
                        'PROVIDER_KEY' => 'PAYMENT',
                        'PROVIDER_VALUE' => 'ACCOUNT_NUMBER'
                    ]
                ]
            ]
        ];

        $handlerResult = $handlerService->add($handlerName, $handlerCode, $handlerSettings);
        return $handlerCode;
    }

    /**
     * Delete test handler
     */
    private function deleteTestHandlerByCode(string $handlerCode): void
    {
        try {
            $handlerService = Fabric::getServiceBuilder()->getPaysystemScope()->handler();
            $handlers = $handlerService->list();
            foreach ($handlers->getHandlers() as $handler) {
                if ($handler->CODE === $handlerCode) {
                    $handlerService->delete(intval($handler->ID));
                    break;
                }
            }
        } catch (BaseException $e) {
            // Log the error but don't fail the test if handler deletion fails
            // This is cleanup code, so failures should not break tests
            error_log("Warning: Failed to delete test handler {$handlerCode}: " . $e->getMessage());
        }
    }

    /**
     * Test batch list payment systems
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testBatchList(): void
    {
        $personTypeId = $this->getPersonTypeId();
        $handlerCode = $this->createTestHandler();

        // Add a test payment system first
        $addResult = $this->paysystemService->add([
            'NAME' => 'Test Payment System for Batch List ' . time(),
            'DESCRIPTION' => 'Test payment system for batch list',
            'PERSON_TYPE_ID' => $personTypeId,
            'BX_REST_HANDLER' => $handlerCode,
            'ACTIVE' => 'Y',
            'ENTITY_REGISTRY_TYPE' => 'ORDER',
            'NEW_WINDOW' => 'N',
            'XML_ID' => 'test_batch_list_' . time()
        ]);
        self::assertGreaterThanOrEqual(1, $addResult->getId());

        $cnt = 0;
        foreach ($this->paysystemService->batch->list(['ID', 'NAME'], ['>ID' => 1], ['ID' => 'ASC']) as $item) {
            $cnt++;
        }

        self::assertGreaterThanOrEqual(1, $cnt);

        // Clean up
        $this->paysystemService->delete($addResult->getId());
        $this->deleteTestHandlerByCode($handlerCode);
    }

    /**
     * Test batch add payment systems
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testBatchAdd(): void
    {
        $handlerCode = $this->createTestHandler();
        $personTypeId = $this->getPersonTypeId();
        $paysystems = [];
        $timestamp = time();
        
        for ($i = 1; $i <= 10; $i++) {
            $paysystems[] = [
                'NAME' => 'Batch Test Payment System ' . $i . ' ' . $timestamp,
                'DESCRIPTION' => 'Batch test payment system ' . $i,
                'PERSON_TYPE_ID' => $personTypeId,
                'BX_REST_HANDLER' => $handlerCode,
                'ACTIVE' => 'Y',
                'ENTITY_REGISTRY_TYPE' => 'ORDER',
                'NEW_WINDOW' => 'N',
                'XML_ID' => 'test_batch_ps_' . $i . '_' . $timestamp
            ];
        }

        $cnt = 0;
        $createdIds = [];
        foreach ($this->paysystemService->batch->add($paysystems) as $item) {
            $cnt++;
            $paysystemId = $item->getId();
            self::assertGreaterThanOrEqual(1, $paysystemId);
            $createdIds[] = $paysystemId;
        }

        self::assertEquals(count($paysystems), $cnt);
        
        // Clean up created payment systems
        foreach ($createdIds as $id) {
            $this->paysystemService->delete($id);
        }
        $this->deleteTestHandlerByCode($handlerCode);
    }

    /**
     * Test batch update and delete payment systems
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testBatchUpdate(): void
    {
        $handlerCode = $this->createTestHandler();
        $personTypeId = $this->getPersonTypeId();
        
        // Add payment systems first
        $paysystems = [];
        $timestamp = time();
        
        for ($i = 1; $i <= self::TEST_SEGMENT_ELEMENTS_COUNT; $i++) {
            $paysystems[] = [
                'NAME' => 'Batch Update Test PS ' . $i . ' ' . $timestamp,
                'DESCRIPTION' => 'Original description ' . $i,
                'PERSON_TYPE_ID' => $personTypeId,
                'BX_REST_HANDLER' => $handlerCode,
                'ACTIVE' => 'Y',
                'ENTITY_REGISTRY_TYPE' => 'ORDER',
                'NEW_WINDOW' => 'N',
                'XML_ID' => 'batch_update_test_' . $i . '_' . $timestamp
            ];
        }

        $cnt = 0;
        $paysystemIds = [];
        foreach ($this->paysystemService->batch->add($paysystems) as $item) {
            $cnt++;
            $paysystemIds[] = $item->getId();
        }

        self::assertEquals(count($paysystems), $cnt);

        // Generate update data
        $updatePaysystemData = [];
        foreach ($paysystemIds as $index => $id) {
            $updatePaysystemData[$id] = [
                'fields' => [
                    'NAME' => 'Updated Payment System ' . ($index + 1) . ' ' . $timestamp,
                    'DESCRIPTION' => 'Updated description ' . ($index + 1),
                    'SORT' => 200 + $index
                ],
            ];
        }

        // Update payment systems in batch mode
        $cnt = 0;
        foreach ($this->paysystemService->batch->update($updatePaysystemData) as $item) {
            $cnt++;
            $this->assertTrue($item->isSuccess());
        }

        self::assertEquals(count($paysystems), $cnt);

        // Delete payment systems in batch mode
        $cnt = 0;
        foreach ($this->paysystemService->batch->delete($paysystemIds) as $item) {
            $cnt++;
            $this->assertTrue($item->isSuccess());
        }

        self::assertEquals(count($paysystems), $cnt);
        
        // Clean up handler
        $this->deleteTestHandlerByCode($handlerCode);
    }

    /**
     * Test batch delete payment systems
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testBatchDelete(): void
    {
        $handlerCode = $this->createTestHandler();
        $personTypeId = $this->getPersonTypeId();
        
        // Add some payment systems first
        $paysystems = [];
        $timestamp = time();
        
        for ($i = 1; $i <= 5; $i++) {
            $paysystems[] = [
                'NAME' => 'Batch Delete Test PS ' . $i . ' ' . $timestamp,
                'DESCRIPTION' => 'Test payment system for batch deletion ' . $i,
                'PERSON_TYPE_ID' => $personTypeId,
                'BX_REST_HANDLER' => $handlerCode,
                'ACTIVE' => 'Y',
                'ENTITY_REGISTRY_TYPE' => 'ORDER',
                'NEW_WINDOW' => 'N',
                'XML_ID' => 'batch_delete_test_' . $i . '_' . $timestamp
            ];
        }

        $paysystemIds = [];
        foreach ($this->paysystemService->batch->add($paysystems) as $item) {
            $paysystemIds[] = $item->getId();
        }
        
        //echo "\nPaysystem Ids:\n";
        //print_r($paysystemIds);
        
        // Delete payment systems in batch
        $cnt = 0;
        foreach ($this->paysystemService->batch->delete($paysystemIds) as $item) {
            $cnt++;
            $this->assertTrue($item->isSuccess());
        }

        echo "\nCount Ids:\n";
        print_r($cnt);
        
        self::assertEquals(count($paysystems), $cnt);
        
        // Clean up handler
        $this->deleteTestHandlerByCode($handlerCode);
    }

    protected function setUp(): void
    {
        $this->paysystemService = Fabric::getServiceBuilder()->getPaysystemScope()->paysystem();
    }

    protected function tearDown(): void
    {
        // Additional cleanup: remove any remaining test handlers that might have been left
        $this->cleanupTestHandlers();
    }

    /**
     * Clean up any test handlers that might be left over
     */
    private function cleanupTestHandlers(): void
    {
        try {
            $handlerService = Fabric::getServiceBuilder()->getPaysystemScope()->handler();
            $handlers = $handlerService->list();
            foreach ($handlers->getHandlers() as $handler) {
                if (str_contains($handler->CODE, 'test_handler_')) {
                    try {
                        $handlerService->delete(intval($handler->ID));
                    } catch (BaseException $e) {
                        // Ignore individual deletion errors
                        error_log("Warning: Failed to cleanup test handler {$handler->CODE}: " . $e->getMessage());
                    }
                }
            }
        } catch (BaseException $e) {
            // Ignore general cleanup errors
            error_log("Warning: Failed to list handlers during cleanup: " . $e->getMessage());
        }
    }
}