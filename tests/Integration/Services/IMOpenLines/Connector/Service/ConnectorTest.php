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

namespace Bitrix24\SDK\Tests\Integration\Services\IMOpenLines\Connector\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\IMOpenLines\Connector\Result\ConnectorItemResult;
use Bitrix24\SDK\Services\IMOpenLines\Connector\Service\Connector;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

/**
 * Class ConnectorTest
 *
 * Integration tests for IMOpenLines Connector service
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\IMOpenLines\Connector\Service
 */
#[CoversClass(Connector::class)]
#[CoversMethod(Connector::class, 'register')]
#[CoversMethod(Connector::class, 'activate')]
#[CoversMethod(Connector::class, 'status')]
#[CoversMethod(Connector::class, 'setData')]
#[CoversMethod(Connector::class, 'list')]
#[CoversMethod(Connector::class, 'unregister')]
#[CoversMethod(Connector::class, 'sendMessages')]
#[CoversMethod(Connector::class, 'updateMessages')]
#[CoversMethod(Connector::class, 'deleteMessages')]
#[CoversMethod(Connector::class, 'sendStatusDelivery')]
#[CoversMethod(Connector::class, 'sendStatusReading')]
#[CoversMethod(Connector::class, 'setChatName')]
class ConnectorTest extends TestCase
{
    use CustomBitrix24Assertions;

    private Connector $connectorService;

    /**
     * Helper method to create test connector data
     */
    private function getTestConnectorData(): array
    {
        $timestamp = time();
        return [
            'ID' => 'test_connector_' . $timestamp,
            'NAME' => 'Test Connector ' . $timestamp,
            'ICON' => [
                'DATA_IMAGE' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 2L2 7L12 12L22 7L12 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
                'COLOR' => '#1900ff',
                'SIZE' => '90%',
                'POSITION' => 'center'
            ],
            'PLACEMENT_HANDLER' => 'https://example.com/handler',
            'COMMENT' => 'Test connector for integration tests'
        ];
    }

    /**
     * Helper method to get or create open line ID for tests
     *
     * @throws BaseException
     * @throws TransportException
     */
    private function getOpenLineId(): string
    {
        // For testing purposes, we'll use a default line ID
        // In real implementation, this would fetch existing open lines
        return '1';
    }

    /**
     * Helper method to create test messages data
     */
    private function getTestMessagesData(): array
    {
        $timestamp = time();
        return [
            [
                'user' => [
                    'id' => 'test_user_' . $timestamp,
                    'name' => 'Test User',
                    'last_name' => 'Connector',
                    'skip_phone_validate' => 'Y'
                ],
                'message' => [
                    'id' => 'test_message_' . $timestamp,
                    'date' => $timestamp,
                    'text' => 'Test message from connector'
                ],
                'chat' => [
                    'id' => 'test_chat_' . $timestamp,
                    'name' => 'Test Chat'
                ]
            ]
        ];
    }

    /**
     * Test list connectors
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testList(): void
    {
        $connectorsResult = $this->connectorService->list();
        $connectors = $connectorsResult->getConnectors();

        self::assertIsArray($connectors);
        
        foreach ($connectors as $connector) {
            self::assertInstanceOf(ConnectorItemResult::class, $connector);
            self::assertNotEmpty($connector->id);
            self::assertNotEmpty($connector->name);
        }
    }

    /**
     * Test register connector
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testRegister(): void
    {
        $connectorData = $this->getTestConnectorData();
        
        $registerResult = $this->connectorService->register($connectorData);
        
        self::assertTrue($registerResult->isSuccess());
        
        // Clean up: unregister the test connector
        try {
            $this->connectorService->unregister($connectorData['ID']);
        } catch (\Exception) {
            // Ignore cleanup errors
        }
    }

    /**
     * Test activate connector
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testActivate(): void
    {
        $connectorData = $this->getTestConnectorData();
        $lineId = $this->getOpenLineId();
        
        // First register the connector
        $this->connectorService->register($connectorData);
        
        try {
            // Test activation
            $activateResult = $this->connectorService->activate($connectorData['ID'], $lineId, 1);
            
            self::assertTrue($activateResult->isSuccess());
            
            // Test deactivation
            $deactivateResult = $this->connectorService->activate($connectorData['ID'], $lineId, 0);
            
            self::assertTrue($deactivateResult->isSuccess());
            
        } finally {
            // Clean up
            try {
                $this->connectorService->unregister($connectorData['ID']);
            } catch (\Exception) {
                // Ignore cleanup errors
            }
        }
    }

    /**
     * Test connector status
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testStatus(): void
    {
        $connectorData = $this->getTestConnectorData();
        $lineId = $this->getOpenLineId();
        
        // First register the connector
        $this->connectorService->register($connectorData);
        
        try {
            $statusResult = $this->connectorService->status($lineId, $connectorData['ID']);
            $statusData = $statusResult->getStatus();
            
            self::assertEquals($connectorData['ID'], $statusData->CONNECTOR);
            
        } finally {
            // Clean up
            try {
                $this->connectorService->unregister($connectorData['ID']);
            } catch (\Exception) {
                // Ignore cleanup errors
            }
        }
    }

    /**
     * Test set connector data
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testSetData(): void
    {
        $connectorData = $this->getTestConnectorData();
        $lineId = $this->getOpenLineId();
        
        // First register the connector
        $this->connectorService->register($connectorData);
        
        try {
            $data = [
                'id' => 'test_account_123',
                'url' => 'https://example.com/chat',
                'name' => 'Test Connector Channel'
            ];
            
            $result = $this->connectorService->setData($connectorData['ID'], $lineId, $data);
            
            self::assertTrue($result->isSuccess());
            
        } finally {
            // Clean up
            try {
                $this->connectorService->unregister($connectorData['ID']);
            } catch (\Exception) {
                // Ignore cleanup errors
            }
        }
    }

    /**
     * Test send messages
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testSendMessages(): void
    {
        $connectorData = $this->getTestConnectorData();
        $lineId = $this->getOpenLineId();
        $messages = $this->getTestMessagesData();
        
        // First register the connector
        $this->connectorService->register($connectorData);
        
        try {
            // Activate connector first
            $this->connectorService->activate($connectorData['ID'], $lineId, 1);
            
            $result = $this->connectorService->sendMessages($connectorData['ID'], $lineId, $messages);
            
            self::assertTrue($result->isSuccess());
            
            // Optionally check data if needed
            $data = $result->getData();
            if ($data !== null) {
                self::assertIsArray($data);
            }
            
        } finally {
            // Clean up
            try {
                $this->connectorService->unregister($connectorData['ID']);
            } catch (\Exception) {
                // Ignore cleanup errors
            }
        }
    }

    /**
     * Test update messages
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testUpdateMessages(): void
    {
        $connectorData = $this->getTestConnectorData();
        $lineId = $this->getOpenLineId();
        $messages = $this->getTestMessagesData();
        
        // First register the connector
        $this->connectorService->register($connectorData);
        
        try {
            // Activate connector first
            $this->connectorService->activate($connectorData['ID'], $lineId, 1);
            
            // Update message text
            $messages[0]['message']['text'] = 'Updated test message';
            
            $result = $this->connectorService->updateMessages($connectorData['ID'], $lineId, $messages);
            
            self::assertTrue($result->isSuccess());
            
            // Optionally check data if needed
            $data = $result->getData();
            if ($data !== null) {
                self::assertIsArray($data);
            }
            
        } finally {
            // Clean up
            try {
                $this->connectorService->unregister($connectorData['ID']);
            } catch (\Exception) {
                // Ignore cleanup errors
            }
        }
    }

    /**
     * Test delete messages
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testDeleteMessages(): void
    {
        $connectorData = $this->getTestConnectorData();
        $lineId = $this->getOpenLineId();
        $timestamp = time();
        
        $deleteMessages = [
            [
                'user' => ['id' => 'test_user_' . $timestamp],
                'message' => ['id' => 'test_message_' . $timestamp],
                'chat' => ['id' => 'test_chat_' . $timestamp]
            ]
        ];
        
        // First register the connector
        $this->connectorService->register($connectorData);
        
        try {
            // Activate connector first
            $this->connectorService->activate($connectorData['ID'], $lineId, 1);
            
            $result = $this->connectorService->deleteMessages($connectorData['ID'], $lineId, $deleteMessages);
            
            self::assertTrue($result->isSuccess());
            
            // Optionally check data if needed
            $data = $result->getData();
            if ($data !== null) {
                self::assertIsArray($data);
            }
            
        } finally {
            // Clean up
            try {
                $this->connectorService->unregister($connectorData['ID']);
            } catch (\Exception) {
                // Ignore cleanup errors
            }
        }
    }

    /**
     * Test send status delivery
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testSendStatusDelivery(): void
    {
        $connectorData = $this->getTestConnectorData();
        $lineId = $this->getOpenLineId();
        $timestamp = time();
        
        $statusMessages = [
            [
                'im' => 'test_im_element',
                'message' => ['id' => ['test_message_' . $timestamp]],
                'chat' => ['id' => 'test_chat_' . $timestamp]
            ]
        ];
        
        // First register the connector
        $this->connectorService->register($connectorData);
        
        try {
            // Activate connector first
            $this->connectorService->activate($connectorData['ID'], $lineId, 1);
            
            $result = $this->connectorService->sendStatusDelivery($connectorData['ID'], $lineId, $statusMessages);
            
            self::assertTrue($result->isSuccess());
            
        } finally {
            // Clean up
            try {
                $this->connectorService->unregister($connectorData['ID']);
            } catch (\Exception) {
                // Ignore cleanup errors
            }
        }
    }

    /**
     * Test send status reading
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testSendStatusReading(): void
    {
        $connectorData = $this->getTestConnectorData();
        $lineId = $this->getOpenLineId();
        $timestamp = time();
        
        $statusMessages = [
            [
                'im' => 'test_im_element',
                'message' => ['id' => ['test_message_' . $timestamp]],
                'chat' => ['id' => 'test_chat_' . $timestamp]
            ]
        ];
        
        // First register the connector
        $this->connectorService->register($connectorData);
        
        try {
            // Activate connector first
            $this->connectorService->activate($connectorData['ID'], $lineId, 1);
            
            $result = $this->connectorService->sendStatusReading($connectorData['ID'], $lineId, $statusMessages);
            
            self::assertTrue($result->isSuccess());
            
        } finally {
            // Clean up
            try {
                $this->connectorService->unregister($connectorData['ID']);
            } catch (\Exception) {
                // Ignore cleanup errors
            }
        }
    }

    /**
     * Test set chat name
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testSetChatName(): void
    {
        $connectorData = $this->getTestConnectorData();
        $lineId = $this->getOpenLineId();
        $timestamp = time();
        
        // First register the connector
        $this->connectorService->register($connectorData);
        
        try {
            // Activate connector first
            $this->connectorService->activate($connectorData['ID'], $lineId, 1);
            
            $chatId = 'test_chat_' . $timestamp;
            $newName = 'New Test Chat Name ' . $timestamp;
            
            // Get current user ID
            $userId = (string)Fabric::getServiceBuilder(true)->getMainScope()->main()->getCurrentUserProfile()->getUserProfile()->ID;
            
            $result = $this->connectorService->setChatName($connectorData['ID'], $lineId, $chatId, $newName, $userId);
            
            self::assertTrue($result->isSuccess());
            
        } finally {
            // Clean up
            try {
                $this->connectorService->unregister($connectorData['ID']);
            } catch (\Exception) {
                // Ignore cleanup errors
            }
        }
    }

    /**
     * Test unregister connector
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testUnregister(): void
    {
        $connectorData = $this->getTestConnectorData();
        
        // First register the connector
        $this->connectorService->register($connectorData);
        
        // Then unregister it
        $unregisterResult = $this->connectorService->unregister($connectorData['ID']);
        
        self::assertTrue($unregisterResult->isSuccess());
    }

    #[\Override]
    protected function setUp(): void
    {
        $this->connectorService = Fabric::getServiceBuilder(true)->getIMOpenLinesScope()->connector();
    }

    #[\Override]
    protected function tearDown(): void
    {
        // Clean up any test connectors that might be left
        $this->cleanupTestConnectors();
    }

    /**
     * Clean up any test connectors that might be left over
     */
    private function cleanupTestConnectors(): void
    {
        try {
            // List all connectors and remove test ones
            $connectorsResult = $this->connectorService->list();
            $connectors = $connectorsResult->getConnectors();
            
            foreach ($connectors as $connector) {
                if (str_contains($connector->id, 'test_connector_')) {
                    try {
                        $this->connectorService->unregister($connector->id);
                    } catch (\Exception) {
                        // Ignore individual deletion errors
                    }
                }
            }
        } catch (\Exception) {
            // Ignore general cleanup errors
        }
    }
}