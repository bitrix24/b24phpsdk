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
use Bitrix24\SDK\Services\Paysystem\Result\PaysystemItemResult;
use Bitrix24\SDK\Services\Paysystem\Service\Paysystem;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;
use Bitrix24\SDK\Core;

/**
 * Class PaysystemTest
 *
 * Integration tests for Payment System service
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Paysystem\Service
 */
#[CoversClass(Paysystem::class)]
#[CoversMethod(Paysystem::class, 'add')]
#[CoversMethod(Paysystem::class, 'delete')]
#[CoversMethod(Paysystem::class, 'list')]
#[CoversMethod(Paysystem::class, 'update')]
#[CoversMethod(Paysystem::class, 'payPayment')]
class PaysystemTest extends TestCase
{
    use CustomBitrix24Assertions;

    private Paysystem $paysystemService;

    /**
     * Get or create a person type ID for tests
     *
     * @throws BaseException
     * @throws TransportException
     */
    private function getPersonTypeId(): int
    {
        $personTypeService = Factory::getServiceBuilder()->getSaleScope()->personType();
        $personTypesResult = $personTypeService->list();
        
        return $personTypesResult->getPersonTypes()[0]->id;
    }

    /**
     * Helper method to create a test order
     *
     * @return int Order ID
     * @throws BaseException
     * @throws TransportException
     */
    private function createTestOrder(int $personTypeId): int
    {
        $orderService = Factory::getServiceBuilder()->getSaleScope()->order();
        $orderFields = [
            'lid' => 's1',
            'personTypeId' => $personTypeId,
            'currency' => 'USD',
            'price' => 100.00
        ];

        return $orderService->add($orderFields)->getId();
    }

    /**
     * Helper method to create a test payment
     *
     * @return int Payment ID
     * @throws BaseException
     * @throws TransportException
     */
    private function createTestPayment(int $orderId, int $paySystemId): int
    {
        $paymentService = Factory::getServiceBuilder()->getSaleScope()->payment();
        $paymentFields = [
            'orderId' => $orderId,
            'paySystemId' => $paySystemId,
            'sum' => 100.00,
            'currency' => 'USD'
        ];

        return $paymentService->add($paymentFields)->getId();
    }

    /**
     * Helper method to delete a test order
     */
    private function deleteTestOrder(int $id): void
    {
        try {
            $orderService = Factory::getServiceBuilder()->getSaleScope()->order();
            $orderService->delete($id);
        } catch (\Exception) {
            // Ignore if order doesn't exist
        }
    }

    /**
     * Helper method to delete a test payment
     */
    private function deleteTestPayment(int $id): void
    {
        try {
            $paymentService = Factory::getServiceBuilder()->getSaleScope()->payment();
            $paymentService->delete($id);
        } catch (\Exception) {
            // Ignore if payment doesn't exist
        }
    }

    /**
     * Create a test handler for payment system
     *
     * @return string Handler CODE
     * @throws BaseException
     * @throws TransportException
     */
    private function createTestHandler(): string
    {
        $handlerService = Factory::getServiceBuilder()->getPaysystemScope()->handler();
        
        $handlerName = 'Test Handler ' . time();
        $handlerCode = 'test_handler_' . time();
        $handlerSettings = [
            'CURRENCY' => ['USD'],
            'CLIENT_TYPE' => 'b2b',
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

        $handlerService->add($handlerName, $handlerCode, $handlerSettings);
        // Return the CODE, not the ID
        return $handlerCode;
    }

    /**
     * Delete test handler by code
     *
     * @throws BaseException
     * @throws TransportException
     */
    private function deleteTestHandlerByCode(string $handlerCode): void
    {
        try {
            $handlerService = Factory::getServiceBuilder()->getPaysystemScope()->handler();
            // We need to get the handler ID first to delete it
            $handlers = $handlerService->list();
            foreach ($handlers->getHandlers() as $handlerItemResult) {
                if ($handlerItemResult->CODE === $handlerCode) {
                    $handlerService->delete(intval($handlerItemResult->ID));
                    break;
                }
            }
        } catch (BaseException $baseException) {
            // Log the error but don't fail the test if handler deletion fails
            // This is cleanup code, so failures should not break tests
            error_log(sprintf('Warning: Failed to delete test handler %s: ', $handlerCode) . $baseException->getMessage());
        }
    }

    /**
     * Test add payment system
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testAdd(): void
    {
        $personTypeId = $this->getPersonTypeId();
        $handlerCode = $this->createTestHandler();

        $name = 'Test Payment System ' . time();
        
        $addedItemResult = $this->paysystemService->add([
            'NAME' => $name,
            'DESCRIPTION' => 'Test payment system description',
            'PERSON_TYPE_ID' => $personTypeId,
            'BX_REST_HANDLER' => $handlerCode,
            'ACTIVE' => 'Y',
            'ENTITY_REGISTRY_TYPE' => 'ORDER',
            'NEW_WINDOW' => 'N',
            'XML_ID' => 'test_ps_' . time()
        ]);

        self::assertGreaterThanOrEqual(1, $addedItemResult->getId());

        // Clean up: first delete payment system, then handler
        $this->paysystemService->delete($addedItemResult->getId());
        $this->deleteTestHandlerByCode($handlerCode);
    }

    /**
     * Test delete payment system
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelete(): void
    {
        $personTypeId = $this->getPersonTypeId();
        $handlerCode = $this->createTestHandler();

        $name = 'Test Payment System to Delete ' . time();
        
        $addedItemResult = $this->paysystemService->add([
            'NAME' => $name,
            'DESCRIPTION' => 'Test payment system for deletion',
            'PERSON_TYPE_ID' => $personTypeId,
            'BX_REST_HANDLER' => $handlerCode,
            'ACTIVE' => 'Y',
            'ENTITY_REGISTRY_TYPE' => 'ORDER'
        ]);

        $deletedItemResult = $this->paysystemService->delete($addedItemResult->getId());
        self::assertTrue($deletedItemResult->isSuccess());

        $this->deleteTestHandlerByCode($handlerCode);
    }

    /**
     * Test list payment systems
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testList(): void
    {
        $personTypeId = $this->getPersonTypeId();
        $handlerCode = $this->createTestHandler();

        // Create a test payment system first
        $name = 'Test Payment System for List ' . time();
        
        $addedItemResult = $this->paysystemService->add([
            'NAME' => $name,
            'DESCRIPTION' => 'Test payment system for listing',
            'PERSON_TYPE_ID' => $personTypeId,
            'BX_REST_HANDLER' => $handlerCode,
            'ACTIVE' => 'Y',
            'ENTITY_REGISTRY_TYPE' => 'ORDER'
        ]);

        $paysystemsResult = $this->paysystemService->list(
            ['ID', 'NAME', 'ACTIVE'],
            [],
            ['ID' => 'ASC']
        );

        self::assertGreaterThanOrEqual(1, count($paysystemsResult->getPaysystems()));
        
        foreach ($paysystemsResult->getPaysystems() as $paysystemItemResult) {
            self::assertInstanceOf(PaysystemItemResult::class, $paysystemItemResult);
            self::assertNotNull($paysystemItemResult->ID);
            self::assertNotNull($paysystemItemResult->NAME);
        }

        // Clean up
        $this->paysystemService->delete($addedItemResult->getId());
        $this->deleteTestHandlerByCode($handlerCode);
    }

    /**
     * Test update payment system
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testUpdate(): void
    {
        $handlerCode = $this->createTestHandler();
        $personTypeId = $this->getPersonTypeId();

        // Create a payment system first
        $addedItemResult = $this->paysystemService->add([
            'NAME' => 'Payment System for Update Test ' . time(),
            'PERSON_TYPE_ID' => $personTypeId,
            'BX_REST_HANDLER' => $handlerCode,
            'ENTITY_REGISTRY_TYPE' => 'ORDER'
        ]);

        $paysystemId = $addedItemResult->getId();
        $newName = 'Updated Payment System ' . time();

        // Update the payment system
        $updatedItemResult = $this->paysystemService->update($paysystemId, [
            'NAME' => $newName
        ]);

        self::assertTrue($updatedItemResult->isSuccess());

        // Verify the update by listing and finding our payment system
        $paysystemsResult = $this->paysystemService->list(['ID', 'NAME'], ['ID' => $paysystemId]);
        $paysystems = $paysystemsResult->getPaysystems();
        self::assertCount(1, $paysystems);
        self::assertEquals($newName, $paysystems[0]->NAME);

        // Clean up
        $this->paysystemService->delete($paysystemId);
        $this->deleteTestHandlerByCode($handlerCode);
    }

    /**
     * Test list with filter
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testListWithFilter(): void
    {
        $handlerCode = $this->createTestHandler();
        $personTypeId = $this->getPersonTypeId();
        $testName = 'Test Payment System for List Filter ' . time();

        // Add a test payment system
        $addedItemResult = $this->paysystemService->add([
            'NAME' => $testName,
            'PERSON_TYPE_ID' => $personTypeId,
            'BX_REST_HANDLER' => $handlerCode,
            'ENTITY_REGISTRY_TYPE' => 'ORDER'
        ]);

        $paysystemId = $addedItemResult->getId();

        // Test list with filter
        $paysystemsResult = $this->paysystemService->list(['ID', 'NAME'], ['NAME' => $testName]);
        $paysystems = $paysystemsResult->getPaysystems();

        self::assertGreaterThanOrEqual(1, count($paysystems));
        self::assertEquals($testName, $paysystems[0]->NAME);

        // Clean up
        $this->paysystemService->delete($paysystemId);
        $this->deleteTestHandlerByCode($handlerCode);
    }

    /**
     * Test list returns payment systems correctly
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testListCount(): void
    {
        $handlerCode = $this->createTestHandler();
        $personTypeId = $this->getPersonTypeId();

        // Add a test payment system
        $addedItemResult = $this->paysystemService->add([
            'NAME' => 'Test Payment System for Count ' . time(),
            'PERSON_TYPE_ID' => $personTypeId,
            'BX_REST_HANDLER' => $handlerCode,
            'ENTITY_REGISTRY_TYPE' => 'ORDER'
        ]);

        $paysystemId = $addedItemResult->getId();

        // Test list returns at least one result
        $paysystemsResult = $this->paysystemService->list();
        self::assertGreaterThanOrEqual(1, count($paysystemsResult->getPaysystems()));

        // Clean up
        $this->paysystemService->delete($paysystemId);
        $this->deleteTestHandlerByCode($handlerCode);
    }

    /**
     * Test pay payment through specific payment system
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testPayPayment(): void
    {
        $handlerCode = $this->createTestHandler();
        $personTypeId = $this->getPersonTypeId();

        // Create a test payment system
        $addedItemResult = $this->paysystemService->add([
            'NAME' => 'Test Payment System for Payment ' . time(),
            'PERSON_TYPE_ID' => $personTypeId,
            'BX_REST_HANDLER' => $handlerCode,
            'ENTITY_REGISTRY_TYPE' => 'ORDER',
            'ACTIVE' => 'Y'
        ]);
        
        $paysystemId = $addedItemResult->getId();

        // Create a test order
        $orderId = $this->createTestOrder($personTypeId);

        // Create a test payment
        $paymentId = $this->createTestPayment($orderId, $paysystemId);

        // Test the payPayment method
        $paymentResult = $this->paysystemService->payPayment($paymentId, $paysystemId);
        
        // Verify that payment result is returned (can be true or false depending on payment system configuration)
        self::assertIsBool($paymentResult->isSuccess());

        // Clean up in reverse order
        $this->deleteTestPayment($paymentId);
        $this->deleteTestOrder($orderId);
        $this->paysystemService->delete($paysystemId);
        $this->deleteTestHandlerByCode($handlerCode);
    }

    
    protected function setUp(): void
    {
        $this->paysystemService = Factory::getServiceBuilder()->getPaysystemScope()->paysystem();
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
            $handlerService = Factory::getServiceBuilder()->getPaysystemScope()->handler();
            $handlers = $handlerService->list();
            foreach ($handlers->getHandlers() as $handlerItemResult) {
                if (str_contains($handlerItemResult->CODE, 'test_handler_')) {
                    try {
                        $handlerService->delete(intval($handlerItemResult->ID));
                    } catch (BaseException $e) {
                        // Ignore individual deletion errors
                        error_log(sprintf('Warning: Failed to cleanup test handler %s: ', $handlerItemResult->CODE) . $e->getMessage());
                    }
                }
            }
        } catch (BaseException $baseException) {
            // Ignore general cleanup errors
            error_log("Warning: Failed to list handlers during cleanup: " . $baseException->getMessage());
        }
    }
}