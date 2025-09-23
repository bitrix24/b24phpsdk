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

namespace Bitrix24\SDK\Tests\Integration\Services\Paysystem\Settings\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Paysystem\Settings\Result\SettingsItemResult;
use Bitrix24\SDK\Services\Paysystem\Settings\Service\Settings;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

/**
 * Class SettingsTest
 *
 * Integration tests for Payment System Settings service
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Paysystem\Settings\Service
 */
#[CoversClass(Settings::class)]
#[CoversMethod(Settings::class, 'get')]
#[CoversMethod(Settings::class, 'update')]
#[CoversMethod(Settings::class, 'getForPayment')]
class SettingsTest extends TestCase
{
    use CustomBitrix24Assertions;

    private Settings $settingsService;
    private array $testDataCleanup = [];

    /**
     * Get or create a person type ID for tests
     *
     * @return int
     * @throws BaseException
     * @throws TransportException
     */
    private function getPersonTypeId(): int
    {
        $personTypeService = Fabric::getServiceBuilder()->getSaleScope()->personType();
        $personTypes = $personTypeService->list();
        
        return $personTypes->getPersonTypes()[0]->id;
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
        $handlerService = Fabric::getServiceBuilder()->getPaysystemScope()->handler();
        
        $handlerName = 'Test Settings Handler ' . time();
        $handlerCode = 'test_settings_handler_' . time();
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
                'TEST_API_KEY' => [
                    'NAME' => 'Test API Key',
                    'DESCRIPTION' => 'API key for testing',
                    'SORT' => '100',
                    'GROUP' => 'CONNECT',
                    'DEFAULT' => [
                        'PROVIDER_KEY' => 'VALUE',
                        'PROVIDER_VALUE' => ''
                    ]
                ],
                'TEST_MERCHANT_ID' => [
                    'NAME' => 'Test Merchant ID',
                    'DESCRIPTION' => 'Merchant identifier for testing',
                    'SORT' => '200',
                    'GROUP' => 'CONNECT',
                    'DEFAULT' => [
                        'PROVIDER_KEY' => 'VALUE',
                        'PROVIDER_VALUE' => ''
                    ]
                ],
                'TEST_MODE' => [
                    'NAME' => 'Test Mode',
                    'DESCRIPTION' => 'Test or production mode',
                    'SORT' => '300',
                    'GROUP' => 'GENERAL',
                    'DEFAULT' => [
                        'PROVIDER_KEY' => 'VALUE',
                        'PROVIDER_VALUE' => 'TEST'
                    ]
                ]
            ]
        ];

        $handlerService->add($handlerName, $handlerCode, $handlerSettings);
        $this->testDataCleanup['handler_code'] = $handlerCode;
        
        return $handlerCode;
    }

    /**
     * Create a test payment system
     *
     * @param string $handlerCode
     * @return int Payment system ID
     * @throws BaseException
     * @throws TransportException
     */
    private function createTestPaymentSystem(string $handlerCode): int
    {
        $paysystemService = Fabric::getServiceBuilder()->getPaysystemScope()->paysystem();
        $personTypeId = $this->getPersonTypeId();
        
        $name = 'Test Payment System for Settings ' . time();
        
        $result = $paysystemService->add([
            'NAME' => $name,
            'DESCRIPTION' => 'Test payment system for settings testing',
            'PERSON_TYPE_ID' => $personTypeId,
            'BX_REST_HANDLER' => $handlerCode,
            'ACTIVE' => 'Y',
            'ENTITY_REGISTRY_TYPE' => 'ORDER',
            'NEW_WINDOW' => 'N',
            'XML_ID' => 'test_settings_ps_' . time()
        ]);

        $paySystemId = $result->getId();
        $this->testDataCleanup['paysystem_id'] = $paySystemId;
        
        return $paySystemId;
    }

    /**
     * Helper method to create a test order
     *
     * @param int $personTypeId
     * @return int Order ID
     * @throws BaseException
     * @throws TransportException
     */
    private function createTestOrder(int $personTypeId): int
    {
        $orderService = Fabric::getServiceBuilder()->getSaleScope()->order();
        $orderFields = [
            'lid' => 's1',
            'personTypeId' => $personTypeId,
            'currency' => 'USD',
            'price' => 100.00
        ];

        $orderId = $orderService->add($orderFields)->getId();
        $this->testDataCleanup['order_id'] = $orderId;
        
        return $orderId;
    }

    /**
     * Helper method to create a test payment
     *
     * @param int $orderId
     * @param int $paySystemId
     * @return int Payment ID
     * @throws BaseException
     * @throws TransportException
     */
    private function createTestPayment(int $orderId, int $paySystemId): int
    {
        $paymentService = Fabric::getServiceBuilder()->getSaleScope()->payment();
        $paymentFields = [
            'orderId' => $orderId,
            'paySystemId' => $paySystemId,
            'sum' => 100.00,
            'currency' => 'USD'
        ];

        $paymentId = $paymentService->add($paymentFields)->getId();
        $this->testDataCleanup['payment_id'] = $paymentId;
        
        return $paymentId;
    }

    /**
     * Test get payment system settings
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testGet(): void
    {
        $handlerCode = $this->createTestHandler();
        $paySystemId = $this->createTestPaymentSystem($handlerCode);
        $personTypeId = $this->getPersonTypeId();

        $result = $this->settingsService->get($paySystemId, $personTypeId);

        self::assertInstanceOf(SettingsItemResult::class, $result);
        // The settings should contain the default values from the handler
        $settings = iterator_to_array($result->getIterator());
        self::assertIsArray($settings);
        
        // Check that we can access settings as properties
        self::assertNotNull($result->TEST_MODE ?? null);
    }

    /**
     * Test update payment system settings
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testUpdate(): void
    {
        $handlerCode = $this->createTestHandler();
        $paySystemId = $this->createTestPaymentSystem($handlerCode);
        $personTypeId = $this->getPersonTypeId();

        // First, get current settings to ensure we have a baseline
        $currentSettings = $this->settingsService->get($paySystemId, $personTypeId);
        
        // Update settings
        $newSettings = [
            'TEST_API_KEY' => [
                'TYPE' => 'VALUE',
                'VALUE' => 'test_api_key_' . time()
            ],
            'TEST_MERCHANT_ID' => [
                'TYPE' => 'VALUE', 
                'VALUE' => 'test_merchant_' . time()
            ]
        ];

        $updateResult = $this->settingsService->update($paySystemId, $newSettings, $personTypeId);
        self::assertTrue($updateResult->isSuccess());

        // Verify settings were updated
        $updatedSettings = $this->settingsService->get($paySystemId, $personTypeId);
        
        // Debug: check what we actually got
        $settingsArray = iterator_to_array($updatedSettings->getIterator());
        
        // Check that settings exist and have correct values
        self::assertArrayHasKey('TEST_API_KEY', $settingsArray);
        self::assertArrayHasKey('TEST_MERCHANT_ID', $settingsArray);
        
        // Compare values directly from array
        self::assertEquals($newSettings['TEST_API_KEY']['VALUE'], $settingsArray['TEST_API_KEY']['VALUE']);
        self::assertEquals($newSettings['TEST_MERCHANT_ID']['VALUE'], $settingsArray['TEST_MERCHANT_ID']['VALUE']);
    }

    /**
     * Test get payment system settings for specific payment
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetForPayment(): void
    {
        $handlerCode = $this->createTestHandler();
        $paySystemId = $this->createTestPaymentSystem($handlerCode);
        $personTypeId = $this->getPersonTypeId();
        
        // Create test order and payment
        $orderId = $this->createTestOrder($personTypeId);
        $paymentId = $this->createTestPayment($orderId, $paySystemId);

        $result = $this->settingsService->getForPayment($paymentId, $paySystemId);

        self::assertInstanceOf(SettingsItemResult::class, $result);
        
        // The settings should contain the values from the payment system
        $settings = iterator_to_array($result->getIterator());
        self::assertIsArray($settings);
        
        // Check that we can access settings as properties
        self::assertNotNull($result->TEST_MODE ?? null);
    }

    /**
     * Test get with default person type (ID = 0)
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetWithDefaultPersonType(): void
    {
        $handlerCode = $this->createTestHandler();
        $paySystemId = $this->createTestPaymentSystem($handlerCode);

        $result = $this->settingsService->get($paySystemId, 0);

        self::assertInstanceOf(SettingsItemResult::class, $result);
        $settings = iterator_to_array($result->getIterator());
        self::assertIsArray($settings);
    }

    protected function setUp(): void
    {
        $this->settingsService = Fabric::getServiceBuilder()->getPaysystemScope()->settings();
        $this->testDataCleanup = [];
    }

    protected function tearDown(): void
    {
        // Clean up test data in reverse order of creation
        $this->cleanupTestData();
    }

    /**
     * Clean up test data created during tests
     */
    private function cleanupTestData(): void
    {
        try {
            // Delete payment first (if exists)
            if (isset($this->testDataCleanup['payment_id'])) {
                $paymentService = Fabric::getServiceBuilder()->getSaleScope()->payment();
                try {
                    $paymentService->delete($this->testDataCleanup['payment_id']);
                } catch (\Exception $e) {
                    error_log("Warning: Failed to delete test payment: " . $e->getMessage());
                }
            }

            // Delete order (if exists)
            if (isset($this->testDataCleanup['order_id'])) {
                $orderService = Fabric::getServiceBuilder()->getSaleScope()->order();
                try {
                    $orderService->delete($this->testDataCleanup['order_id']);
                } catch (\Exception $e) {
                    error_log("Warning: Failed to delete test order: " . $e->getMessage());
                }
            }

            // Delete payment system (if exists)
            if (isset($this->testDataCleanup['paysystem_id'])) {
                $paysystemService = Fabric::getServiceBuilder()->getPaysystemScope()->paysystem();
                try {
                    $paysystemService->delete($this->testDataCleanup['paysystem_id']);
                } catch (\Exception $e) {
                    error_log("Warning: Failed to delete test payment system: " . $e->getMessage());
                }
            }

            // Delete handler last (if exists)
            if (isset($this->testDataCleanup['handler_code'])) {
                $handlerService = Fabric::getServiceBuilder()->getPaysystemScope()->handler();
                try {
                    // We need to get the handler ID first to delete it
                    $handlers = $handlerService->list();
                    foreach ($handlers->getHandlers() as $handler) {
                        if ($handler->CODE === $this->testDataCleanup['handler_code']) {
                            $handlerService->delete(intval($handler->ID));
                            break;
                        }
                    }
                } catch (\Exception $e) {
                    error_log("Warning: Failed to delete test handler: " . $e->getMessage());
                }
            }
        } catch (\Exception $e) {
            error_log("Warning: General cleanup error: " . $e->getMessage());
        }
    }
}