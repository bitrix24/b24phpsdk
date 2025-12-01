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

namespace Bitrix24\SDK\Tests\Integration\Services\Paysystem\Handler\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Paysystem\Handler\Service\Handler;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

/**
 * Class HandlerTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Paysystem\Handler\Service
 */
#[CoversMethod(Handler::class, 'add')]
#[CoversMethod(Handler::class, 'update')]
#[CoversMethod(Handler::class, 'list')]
#[CoversMethod(Handler::class, 'delete')]
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\Paysystem\Handler\Service\Handler::class)]
class HandlerTest extends TestCase
{
    protected Handler $handlerService;

    protected function setUp(): void
    {
        $this->handlerService = Factory::getServiceBuilder()->getPaysystemScope()->handler();
    }

    /**
     * Get default handler settings for tests
     */
    private function getDefaultHandlerSettings(): array
    {
        return [
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
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAdd(): void
    {
        // Create a payment system handler
        $handlerName = 'Test Handler ' . time();
        $handlerCode = 'test_handler_' . time();
        $handlerSettings = $this->getDefaultHandlerSettings();

        $addedItemResult = $this->handlerService->add($handlerName, $handlerCode, $handlerSettings);
        $handlerId = $addedItemResult->getId();

        self::assertGreaterThan(0, $handlerId);

        // Clean up
        $this->handlerService->delete($handlerId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testUpdate(): void
    {
        // Create a handler first
        $handlerName = 'Test Handler ' . time();
        $handlerCode = 'test_handler_' . time();
        $handlerSettings = $this->getDefaultHandlerSettings();

        $addedItemResult = $this->handlerService->add($handlerName, $handlerCode, $handlerSettings);
        $handlerId = $addedItemResult->getId();

        // Update the handler
        $updateFields = [
            'NAME' => 'Updated Test Handler ' . time(),
            'SORT' => 200
        ];

        $updatedItemResult = $this->handlerService->update($handlerId, $updateFields);
        self::assertTrue($updatedItemResult->isSuccess());

        // Clean up
        $this->handlerService->delete($handlerId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testList(): void
    {
        // Create a handler first
        $handlerName = 'Test Handler ' . time();
        $handlerCode = 'test_handler_' . time();
        $handlerSettings = $this->getDefaultHandlerSettings();

        $addedItemResult = $this->handlerService->add($handlerName, $handlerCode, $handlerSettings);
        $handlerId = $addedItemResult->getId();

        // Test list functionality
        $handlersResult = $this->handlerService->list();
        $handlers = $handlersResult->getHandlers();

        self::assertIsArray($handlers);
        
        // Find our created handler
        $foundHandler = null;
        foreach ($handlers as $handler) {
            if ($handler->ID === (string)$handlerId) {
                $foundHandler = $handler;
                break;
            }
        }

        self::assertNotNull($foundHandler);
        self::assertEquals($handlerName, $foundHandler->NAME);
        self::assertEquals($handlerCode, $foundHandler->CODE);

        // Clean up
        $this->handlerService->delete($handlerId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelete(): void
    {
        // Create a handler first
        $handlerName = 'Test Handler ' . time();
        $handlerCode = 'test_handler_' . time();
        $handlerSettings = $this->getDefaultHandlerSettings();

        $addedItemResult = $this->handlerService->add($handlerName, $handlerCode, $handlerSettings);
        $handlerId = $addedItemResult->getId();

        // Delete the handler
        $deletedItemResult = $this->handlerService->delete($handlerId);
        self::assertTrue($deletedItemResult->isSuccess());
    }
}