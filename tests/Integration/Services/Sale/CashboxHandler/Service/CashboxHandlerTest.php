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

namespace Bitrix24\SDK\Tests\Integration\Services\Sale\CashboxHandler\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Sale\CashboxHandler\Service\CashboxHandler;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

/**
 * Class CashboxHandlerTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Sale\CashboxHandler\Service
 */
#[CoversMethod(CashboxHandler::class, 'add')]
#[CoversMethod(CashboxHandler::class, 'update')]
#[CoversMethod(CashboxHandler::class, 'list')]
#[CoversMethod(CashboxHandler::class, 'delete')]
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\Sale\CashboxHandler\Service\CashboxHandler::class)]
class CashboxHandlerTest extends TestCase
{
    protected CashboxHandler $cashboxHandlerService;

    protected function setUp(): void
    {
        $this->cashboxHandlerService = Factory::getServiceBuilder()->getSaleScope()->cashboxHandler();
    }

    /**
     * Generate test settings for cashbox handler
     */
    protected function getTestSettings(): array
    {
        return [
            'PRINT_URL' => 'https://example.com/print_receipt.php',
            'CHECK_URL' => 'https://example.com/check_receipt.php',
            'HTTP_VERSION' => '1.1',
            'CONFIG' => [
                'AUTH' => [
                    'LABEL' => 'Authorization',
                    'ITEMS' => [
                        'LOGIN' => [
                            'TYPE' => 'STRING',
                            'LABEL' => 'Login',
                            'REQUIRED' => 'Y'
                        ],
                        'PASSWORD' => [
                            'TYPE' => 'STRING',
                            'LABEL' => 'Password',
                            'REQUIRED' => 'Y'
                        ]
                    ]
                ],
                'COMPANY' => [
                    'LABEL' => 'Company Information',
                    'ITEMS' => [
                        'INN' => [
                            'TYPE' => 'STRING',
                            'LABEL' => 'Company INN',
                            'REQUIRED' => 'Y'
                        ]
                    ]
                ],
                'INTERACTION' => [
                    'LABEL' => 'Cashbox Interaction Settings',
                    'ITEMS' => [
                        'MODE' => [
                            'TYPE' => 'ENUM',
                            'LABEL' => 'Cashbox Operating Mode',
                            'OPTIONS' => [
                                'ACTIVE' => 'live',
                                'TEST' => 'test'
                            ]
                        ]
                    ]
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
        $code = 'test_cashbox_handler_' . time();
        $name = 'Test Cashbox Handler';
        $settings = $this->getTestSettings();

        $addedItemResult = $this->cashboxHandlerService->add(
            $code,
            $name,
            $settings,
            100,
            'N'
        );

        $handlerId = $addedItemResult->getId();
        self::assertGreaterThan(0, $handlerId);

        // Clean up
        $this->cashboxHandlerService->delete($handlerId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testUpdate(): void
    {
        // Create a cashbox handler
        $code = 'test_cashbox_handler_' . time();
        $name = 'Test Cashbox Handler';
        $settings = $this->getTestSettings();

        $addedItemResult = $this->cashboxHandlerService->add(
            $code,
            $name,
            $settings,
            100,
            'N'
        );

        $handlerId = $addedItemResult->getId();

        // Update the handler
        $updateFields = [
            'NAME' => 'Updated Test Cashbox Handler',
            'SORT' => 200
        ];

        $updatedItemResult = $this->cashboxHandlerService->update($handlerId, $updateFields);
        self::assertTrue($updatedItemResult->isSuccess());

        // Verify the update by listing handlers
        $cashboxHandlersResult = $this->cashboxHandlerService->list();
        $handlers = $cashboxHandlersResult->getCashboxHandlers();

        $found = false;
        foreach ($handlers as $handler) {
            if ((int)$handler->ID === $handlerId) {
                self::assertEquals('Updated Test Cashbox Handler', $handler->NAME);
                self::assertEquals(200, (int)$handler->SORT);
                $found = true;
                break;
            }
        }

        self::assertTrue($found, 'Updated handler not found in list');

        // Clean up
        $this->cashboxHandlerService->delete($handlerId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testList(): void
    {
        // Create a cashbox handler
        $code = 'test_cashbox_handler_' . time();
        $name = 'Test List Cashbox Handler';
        $settings = $this->getTestSettings();

        $addedItemResult = $this->cashboxHandlerService->add(
            $code,
            $name,
            $settings,
            100,
            'N'
        );

        $handlerId = $addedItemResult->getId();

        // List handlers
        $cashboxHandlersResult = $this->cashboxHandlerService->list();
        $handlers = $cashboxHandlersResult->getCashboxHandlers();

        self::assertIsArray($handlers);
        self::assertGreaterThan(0, count($handlers));

        // Verify our handler is in the list
        $found = false;
        foreach ($handlers as $handler) {
            if ((int)$handler->ID === $handlerId) {
                self::assertEquals($code, $handler->CODE);
                self::assertEquals($name, $handler->NAME);
                self::assertIsArray($handler->SETTINGS);
                self::assertEquals('https://example.com/print_receipt.php', $handler->SETTINGS['PRINT_URL']);
                self::assertEquals('https://example.com/check_receipt.php', $handler->SETTINGS['CHECK_URL']);
                self::assertEquals('1.1', $handler->SETTINGS['HTTP_VERSION']);
                self::assertIsArray($handler->SETTINGS['CONFIG']);
                $found = true;
                break;
            }
        }

        self::assertTrue($found, 'Created handler not found in list');

        // Clean up
        $this->cashboxHandlerService->delete($handlerId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelete(): void
    {
        // Create a cashbox handler
        $code = 'test_cashbox_handler_' . time();
        $name = 'Test Delete Cashbox Handler';
        $settings = $this->getTestSettings();

        $addedItemResult = $this->cashboxHandlerService->add(
            $code,
            $name,
            $settings,
            100,
            'N'
        );

        $handlerId = $addedItemResult->getId();

        // Delete the handler
        $deletedItemResult = $this->cashboxHandlerService->delete($handlerId);
        self::assertTrue($deletedItemResult->isSuccess());

        // Verify handler no longer exists in the list
        $cashboxHandlersResult = $this->cashboxHandlerService->list();
        $handlers = $cashboxHandlersResult->getCashboxHandlers();

        $found = false;
        foreach ($handlers as $handler) {
            if ((int)$handler->ID === $handlerId) {
                $found = true;
                break;
            }
        }

        self::assertFalse($found, 'Deleted handler still found in list');
    }
}