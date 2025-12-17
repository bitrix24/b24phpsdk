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

namespace Bitrix24\SDK\Tests\Integration\Services\Sale\DeliveryHandler\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Sale\DeliveryHandler\Service\DeliveryHandler;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

/**
 * Class DeliveryHandlerTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Sale\DeliveryHandler\Service
 */
#[CoversMethod(DeliveryHandler::class,'add')]
#[CoversMethod(DeliveryHandler::class,'update')]
#[CoversMethod(DeliveryHandler::class,'list')]
#[CoversMethod(DeliveryHandler::class,'delete')]
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\Sale\DeliveryHandler\Service\DeliveryHandler::class)]
class DeliveryHandlerTest extends TestCase
{
    protected DeliveryHandler $deliveryHandlerService;

    #[\Override]
    protected function setUp(): void
    {
        $this->deliveryHandlerService = Factory::getServiceBuilder()->getSaleScope()->deliveryHandler();
    }

    /**
     * Get sample delivery handler fields for testing
     */
    protected function getSampleDeliveryHandlerFields(): array
    {
        return [
            'NAME' => 'Test Delivery Handler',
            'CODE' => 'test_delivery_handler_' . time(),
            'SORT' => 100,
            'DESCRIPTION' => 'Test delivery handler description',
            'SETTINGS' => [
                'CALCULATE_URL' => 'https://example.com/calculate',
                'CREATE_DELIVERY_REQUEST_URL' => 'https://example.com/create',
                'CANCEL_DELIVERY_REQUEST_URL' => 'https://example.com/cancel',
                'HAS_CALLBACK_TRACKING_SUPPORT' => 'Y',
                'CONFIG' => [
                    [
                        'TYPE' => 'STRING',
                        'CODE' => 'API_KEY',
                        'NAME' => 'API Key'
                    ],
                    [
                        'TYPE' => 'Y/N',
                        'CODE' => 'TEST_MODE',
                        'NAME' => 'Test Mode'
                    ],
                    [
                        'TYPE' => 'NUMBER',
                        'CODE' => 'TIMEOUT',
                        'NAME' => 'Request Timeout'
                    ],
                    [
                        'TYPE' => 'ENUM',
                        'CODE' => 'SERVICE_TYPE',
                        'NAME' => 'Service Type',
                        'OPTIONS' => [
                            'EXPRESS' => 'Express Delivery',
                            'STANDARD' => 'Standard Delivery',
                            'ECONOMY' => 'Economy Delivery'
                        ]
                    ]
                ]
            ],
            'PROFILES' => [
                [
                    'NAME' => 'Express',
                    'CODE' => 'EXPRESS',
                    'DESCRIPTION' => 'Express delivery profile'
                ],
                [
                    'NAME' => 'Standard',
                    'CODE' => 'STANDARD',
                    'DESCRIPTION' => 'Standard delivery profile'
                ]
            ]
        ];
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAdd(): void
    {
        // Create a delivery handler
        $handlerFields = $this->getSampleDeliveryHandlerFields();

        $addedItemResult = $this->deliveryHandlerService->add($handlerFields);
        $handlerId = $addedItemResult->getId();

        self::assertGreaterThan(0, $handlerId);

        // Clean up
        $this->deliveryHandlerService->delete($handlerId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testUpdate(): void
    {
        // Create a delivery handler
        $handlerFields = $this->getSampleDeliveryHandlerFields();

        $addedItemResult = $this->deliveryHandlerService->add($handlerFields);
        $handlerId = $addedItemResult->getId();

        // Update the delivery handler
        $updateFields = [
            'NAME' => 'Updated Test Delivery Handler',
            'DESCRIPTION' => 'Updated description',
            'SORT' => 200
        ];

        $updatedItemResult = $this->deliveryHandlerService->update($handlerId, $updateFields);
        self::assertTrue($updatedItemResult->isSuccess());

        // Verify the update by listing and finding our handler
        $deliveryHandlersResult = $this->deliveryHandlerService->list();
        $handlers = $deliveryHandlersResult->getDeliveryHandlers();

        $found = false;
        foreach ($handlers as $handler) {
            if ((int)$handler->ID === $handlerId) {
                self::assertEquals('Updated Test Delivery Handler', $handler->NAME);
                self::assertEquals('Updated description', $handler->DESCRIPTION);
                self::assertEquals(200, (int)$handler->SORT);
                $found = true;
                break;
            }
        }

        self::assertTrue($found, 'Updated delivery handler should be found in the list');

        // Clean up
        $this->deliveryHandlerService->delete($handlerId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testList(): void
    {
        // Create a delivery handler
        $handlerFields = $this->getSampleDeliveryHandlerFields();

        $addedItemResult = $this->deliveryHandlerService->add($handlerFields);
        $handlerId = $addedItemResult->getId();

        // List delivery handlers
        $deliveryHandlersResult = $this->deliveryHandlerService->list();
        $handlers = $deliveryHandlersResult->getDeliveryHandlers();

        self::assertGreaterThan(0, count($handlers));

        // Verify our handler is in the list
        $found = false;
        foreach ($handlers as $handler) {
            if ((int)$handler->ID === $handlerId) {
                self::assertEquals($handlerFields['NAME'], $handler->NAME);
                self::assertEquals($handlerFields['CODE'], $handler->CODE);
                self::assertEquals($handlerFields['DESCRIPTION'], $handler->DESCRIPTION);
                
                // Verify SETTINGS structure
                self::assertIsArray($handler->SETTINGS);
                self::assertEquals($handlerFields['SETTINGS']['CALCULATE_URL'], $handler->SETTINGS['CALCULATE_URL']);
                self::assertEquals($handlerFields['SETTINGS']['HAS_CALLBACK_TRACKING_SUPPORT'], $handler->SETTINGS['HAS_CALLBACK_TRACKING_SUPPORT']);
                
                // Verify PROFILES structure
                self::assertIsArray($handler->PROFILES);
                self::assertCount(2, $handler->PROFILES);
                
                $found = true;
                break;
            }
        }

        self::assertTrue($found, 'Created delivery handler should be found in the list');

        // Clean up
        $this->deliveryHandlerService->delete($handlerId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelete(): void
    {
        // Create a delivery handler
        $handlerFields = $this->getSampleDeliveryHandlerFields();

        $addedItemResult = $this->deliveryHandlerService->add($handlerFields);
        $handlerId = $addedItemResult->getId();

        // Delete the delivery handler
        $deletedItemResult = $this->deliveryHandlerService->delete($handlerId);
        self::assertTrue($deletedItemResult->isSuccess());

        // Verify handler no longer exists in the list
        $deliveryHandlersResult = $this->deliveryHandlerService->list();
        $handlers = $deliveryHandlersResult->getDeliveryHandlers();

        $found = false;
        foreach ($handlers as $handler) {
            if ((int)$handler->ID === $handlerId) {
                $found = true;
                break;
            }
        }

        self::assertFalse($found, 'Deleted delivery handler should not be found in the list');
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testComplexSettings(): void
    {
        // Test with more complex SETTINGS structure
        $handlerFields = $this->getSampleDeliveryHandlerFields();
        
        // Add more complex CONFIG options
        $handlerFields['SETTINGS']['CONFIG'][] = [
            'TYPE' => 'DATE',
            'CODE' => 'VALID_UNTIL',
            'NAME' => 'Valid Until Date'
        ];
        
        $handlerFields['SETTINGS']['CONFIG'][] = [
            'TYPE' => 'LOCATION',
            'CODE' => 'SERVICE_LOCATION',
            'NAME' => 'Service Location'
        ];

        $addedItemResult = $this->deliveryHandlerService->add($handlerFields);
        $handlerId = $addedItemResult->getId();

        self::assertGreaterThan(0, $handlerId);

        // Verify complex settings were saved correctly
        $deliveryHandlersResult = $this->deliveryHandlerService->list();
        $handlers = $deliveryHandlersResult->getDeliveryHandlers();

        $found = false;
        foreach ($handlers as $handler) {
            if ((int)$handler->ID === $handlerId) {
                self::assertIsArray($handler->SETTINGS['CONFIG']);
                self::assertCount(6, $handler->SETTINGS['CONFIG']); // 4 + 2 additional
                $found = true;
                break;
            }
        }

        self::assertTrue($found, 'Handler with complex settings should be found');

        // Clean up
        $this->deliveryHandlerService->delete($handlerId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testMultipleProfiles(): void
    {
        // Test with multiple profiles
        $handlerFields = $this->getSampleDeliveryHandlerFields();
        
        // Add more profiles
        $handlerFields['PROFILES'][] = [
            'NAME' => 'Economy',
            'CODE' => 'ECONOMY',
            'DESCRIPTION' => 'Economy delivery profile'
        ];
        
        $handlerFields['PROFILES'][] = [
            'NAME' => 'Premium',
            'CODE' => 'PREMIUM',
            'DESCRIPTION' => 'Premium delivery profile'
        ];

        $addedItemResult = $this->deliveryHandlerService->add($handlerFields);
        $handlerId = $addedItemResult->getId();

        self::assertGreaterThan(0, $handlerId);

        // Verify all profiles were saved correctly
        $deliveryHandlersResult = $this->deliveryHandlerService->list();
        $handlers = $deliveryHandlersResult->getDeliveryHandlers();

        $found = false;
        foreach ($handlers as $handler) {
            if ((int)$handler->ID === $handlerId) {
                self::assertIsArray($handler->PROFILES);
                self::assertCount(4, $handler->PROFILES); // 2 + 2 additional
                
                // Check profile names
                $profileNames = array_column($handler->PROFILES, 'NAME');
                self::assertContains('Express', $profileNames);
                self::assertContains('Standard', $profileNames);
                self::assertContains('Economy', $profileNames);
                self::assertContains('Premium', $profileNames);
                
                $found = true;
                break;
            }
        }

        self::assertTrue($found, 'Handler with multiple profiles should be found');

        // Clean up
        $this->deliveryHandlerService->delete($handlerId);
    }
}