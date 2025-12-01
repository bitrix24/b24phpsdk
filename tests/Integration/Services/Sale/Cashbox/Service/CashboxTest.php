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

namespace Bitrix24\SDK\Tests\Integration\Services\Sale\Cashbox\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Sale\Cashbox\Service\Cashbox;
use Bitrix24\SDK\Services\Sale\CashboxHandler\Service\CashboxHandler;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

/**
 * Class CashboxTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Sale\Cashbox\Service
 */
#[CoversMethod(Cashbox::class, 'add')]
#[CoversMethod(Cashbox::class, 'update')]
#[CoversMethod(Cashbox::class, 'list')]
#[CoversMethod(Cashbox::class, 'delete')]
#[CoversMethod(Cashbox::class, 'checkApply')]
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\Sale\Cashbox\Service\Cashbox::class)]
class CashboxTest extends TestCase
{
    protected Cashbox $cashboxService;

    protected CashboxHandler $cashboxHandlerService;

    protected function setUp(): void
    {
        $this->cashboxService = Factory::getServiceBuilder()->getSaleScope()->cashbox();
        $this->cashboxHandlerService = Factory::getServiceBuilder()->getSaleScope()->cashboxHandler();
    }

    /**
     * Generate test settings for cashbox handler
     */
    protected function getTestHandlerSettings(): array
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
                ]
            ]
        ];
    }

    /**
     * Create a cashbox handler and return its code
     */
    protected function createTestCashboxHandler(): array
    {
        $code = 'test_cashbox_handler_' . time();
        $name = 'Test Cashbox Handler';
        $settings = $this->getTestHandlerSettings();

        $addedItemResult = $this->cashboxHandlerService->add(
            $code,
            $name,
            $settings,
            100,
            'N'
        );

        return [
            'id' => $addedItemResult->getId(),
            'code' => $code
        ];
    }

    /**
     * Generate test fields for cashbox creation
     */
    protected function getTestCashboxFields(string $restCode): array
    {
        return [
            'NAME' => 'Test Cash Register ' . time(),
            'REST_CODE' => $restCode,
            'EMAIL' => 'test@example.com',
            'ACTIVE' => 'Y',
            'SORT' => 100,
            'USE_OFFLINE' => 'N',
            'SETTINGS' => [
                'TEST_MODE' => 'Y',
                'TIMEOUT' => 30
            ]
        ];
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAdd(): void
    {
        // Create a cashbox handler first
        $handlerData = $this->createTestCashboxHandler();
        $fields = $this->getTestCashboxFields($handlerData['code']);

        $addedItemResult = $this->cashboxService->add($fields);

        $cashboxId = $addedItemResult->getId();
        self::assertGreaterThan(0, $cashboxId);

        // Clean up
        $this->cashboxService->delete($cashboxId);
        $this->cashboxHandlerService->delete($handlerData['id']);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testUpdate(): void
    {
        // Create a cashbox handler first
        $handlerData = $this->createTestCashboxHandler();
        $fields = $this->getTestCashboxFields($handlerData['code']);

        $addedItemResult = $this->cashboxService->add($fields);
        $cashboxId = $addedItemResult->getId();

        // Update the cashbox
        $updateFields = [
            'NAME' => 'Updated Test Cash Register',
            'ACTIVE' => 'N',
            'SORT' => 200
        ];

        $updatedItemResult = $this->cashboxService->update($cashboxId, $updateFields);
        self::assertTrue($updatedItemResult->isSuccess());

        // Verify the update by listing cashboxes
        $cashboxesResult = $this->cashboxService->list();
        $cashboxes = $cashboxesResult->getCashboxes();

        $found = false;
        foreach ($cashboxes as $cashbox) {
            if ((int)$cashbox->ID === $cashboxId) {
                self::assertEquals('Updated Test Cash Register', $cashbox->NAME);
                self::assertEquals('N', $cashbox->ACTIVE);
                self::assertEquals(200, (int)$cashbox->SORT);
                $found = true;
                break;
            }
        }

        self::assertTrue($found, 'Updated cashbox not found in list');

        // Clean up
        $this->cashboxService->delete($cashboxId);
        $this->cashboxHandlerService->delete($handlerData['id']);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testList(): void
    {
        // Create a cashbox handler first
        $handlerData = $this->createTestCashboxHandler();
        $fields = $this->getTestCashboxFields($handlerData['code']);

        $addedItemResult = $this->cashboxService->add($fields);
        $cashboxId = $addedItemResult->getId();

        // List cashboxes
        $cashboxesResult = $this->cashboxService->list();
        $cashboxes = $cashboxesResult->getCashboxes();

        self::assertIsArray($cashboxes);
        self::assertGreaterThan(0, count($cashboxes));

        // Verify our cashbox is in the list
        $found = false;
        foreach ($cashboxes as $cashbox) {
            if ((int)$cashbox->ID === $cashboxId) {
                self::assertEquals($fields['NAME'], $cashbox->NAME);
                self::assertEquals($fields['EMAIL'], $cashbox->EMAIL);
                self::assertEquals($fields['ACTIVE'], $cashbox->ACTIVE);
                self::assertEquals($fields['SORT'], (int)$cashbox->SORT);
                self::assertEquals($fields['USE_OFFLINE'], $cashbox->USE_OFFLINE);
                // Note: REST_CODE is not returned by the API in list response
                $found = true;
                break;
            }
        }

        self::assertTrue($found, 'Created cashbox not found in list');

        // Clean up
        $this->cashboxService->delete($cashboxId);
        $this->cashboxHandlerService->delete($handlerData['id']);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testListWithFilters(): void
    {
        // Create a cashbox handler first
        $handlerData = $this->createTestCashboxHandler();
        $fields = $this->getTestCashboxFields($handlerData['code']);
        $fields['ACTIVE'] = 'N';

        $addedItemResult = $this->cashboxService->add($fields);
        $cashboxId = $addedItemResult->getId();

        // List only inactive cashboxes
        $cashboxesResult = $this->cashboxService->list(
            ['ID', 'NAME', 'ACTIVE'],
            ['ACTIVE' => 'N'],
            ['ID' => 'DESC']
        );
        $cashboxes = $cashboxesResult->getCashboxes();

        self::assertIsArray($cashboxes);

        // Verify filtering worked
        foreach ($cashboxes as $cashbox) {
            self::assertEquals('N', $cashbox->ACTIVE);
        }

        // Clean up
        $this->cashboxService->delete($cashboxId);
        $this->cashboxHandlerService->delete($handlerData['id']);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelete(): void
    {
        // Create a cashbox handler first
        $handlerData = $this->createTestCashboxHandler();
        $fields = $this->getTestCashboxFields($handlerData['code']);

        $addedItemResult = $this->cashboxService->add($fields);
        $cashboxId = $addedItemResult->getId();

        // Delete the cashbox
        $deletedItemResult = $this->cashboxService->delete($cashboxId);
        self::assertTrue($deletedItemResult->isSuccess());

        // Verify cashbox no longer exists in the list
        $cashboxesResult = $this->cashboxService->list();
        $cashboxes = $cashboxesResult->getCashboxes();

        $found = false;
        foreach ($cashboxes as $cashbox) {
            if ((int)$cashbox->ID === $cashboxId) {
                $found = true;
                break;
            }
        }

        self::assertFalse($found, 'Deleted cashbox still found in list');

        // Clean up handler
        $this->cashboxHandlerService->delete($handlerData['id']);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testCheckApply(): void
    {
        $checkApplyFields = [
            'UUID' => 'test_receipt_' . time(),
            'PRINT_END_TIME' => (string)time(),
            'REG_NUMBER_KKT' => '1234567891011121',
            'FISCAL_DOC_ATTR' => '1234567890',
            'FISCAL_DOC_NUMBER' => '12345',
            'FISCAL_RECEIPT_NUMBER' => '123',
            'FN_NUMBER' => '1234567891011121',
            'SHIFT_NUMBER' => '1'
        ];

        // This test expects an error since we're using a non-existent receipt UUID
        // In a real scenario, the receipt would be created first through the print process
        $this->expectException(\Bitrix24\SDK\Core\Exceptions\BaseException::class);
        $this->expectExceptionMessage('check not found');

        $this->cashboxService->checkApply($checkApplyFields);
    }
}