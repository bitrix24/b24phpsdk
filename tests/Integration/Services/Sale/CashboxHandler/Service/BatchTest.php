<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <algonexys@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\Sale\CashboxHandler\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Sale\CashboxHandler\Service\Batch;
use Bitrix24\SDK\Services\Sale\CashboxHandler\Service\CashboxHandler;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Batch::class)]
#[CoversClass(\Bitrix24\SDK\Services\Sale\CashboxHandler\Batch::class)]
class BatchTest extends TestCase
{
    private const ITEMS_COUNT = 3;

    private CashboxHandler $cashboxHandlerService;

    #[\Override]
    protected function setUp(): void
    {
        $this->cashboxHandlerService = Factory::getServiceBuilder()->getSaleScope()->cashboxHandler();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('Batch::add, Batch::update, Batch::list, Batch::delete cash register handlers')]
    public function testAddUpdateListDelete(): void
    {
        $codePrefix = 'batch_cashbox_handler_' . uniqid() . '_';
        $items = [];
        for ($i = 1; $i <= self::ITEMS_COUNT; $i++) {
            $items[] = [
                'CODE' => $codePrefix . $i,
                'NAME' => 'Batch cashbox handler ' . $i,
                'SORT' => 100,
                'SUPPORTS_FFD105' => 'N',
                'SETTINGS' => [
                    'PRINT_URL' => 'https://example.com/print_receipt.php',
                    'CHECK_URL' => 'https://example.com/check_receipt.php',
                    'HTTP_VERSION' => '1.1',
                    'CONFIG' => [
                        'AUTH' => [
                            'LABEL' => 'Authorization',
                            'ITEMS' => [
                                'LOGIN' => ['TYPE' => 'STRING', 'LABEL' => 'Login', 'REQUIRED' => 'Y'],
                            ],
                        ],
                    ],
                ],
            ];
        }

        $addedIds = [];
        foreach ($this->cashboxHandlerService->batch->add($items) as $addedItemResult) {
            $addedIds[] = $addedItemResult->getId();
        }

        $this->assertCount(self::ITEMS_COUNT, $addedIds);

        $updates = [];
        foreach ($addedIds as $id) {
            $updates[$id] = ['NAME' => 'Updated ' . $id, 'SORT' => 200];
        }

        foreach ($this->cashboxHandlerService->batch->update($updates) as $updatedItemResult) {
            $this->assertTrue($updatedItemResult->isSuccess());
        }

        $listed = [];
        foreach ($this->cashboxHandlerService->batch->list() as $item) {
            $listed[(int)$item->ID] = $item->NAME;
        }

        foreach ($addedIds as $id) {
            $this->assertArrayHasKey($id, $listed);
            $this->assertSame('Updated ' . $id, $listed[$id]);
        }

        $deletedCount = 0;
        foreach ($this->cashboxHandlerService->batch->delete($addedIds) as $deletedItemResult) {
            $this->assertTrue($deletedItemResult->isSuccess());
            $deletedCount++;
        }

        $this->assertSame(self::ITEMS_COUNT, $deletedCount);
    }
}
