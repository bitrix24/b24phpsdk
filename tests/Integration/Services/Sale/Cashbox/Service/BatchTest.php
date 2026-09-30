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

namespace Bitrix24\SDK\Tests\Integration\Services\Sale\Cashbox\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Sale\Cashbox\Service\Batch;
use Bitrix24\SDK\Services\Sale\Cashbox\Service\Cashbox;
use Bitrix24\SDK\Services\Sale\CashboxHandler\Service\CashboxHandler;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Batch::class)]
#[CoversClass(\Bitrix24\SDK\Services\Sale\Cashbox\Batch::class)]
class BatchTest extends TestCase
{
    private const ITEMS_COUNT = 3;

    private Cashbox $cashboxService;

    private CashboxHandler $cashboxHandlerService;

    private int $handlerId;

    private string $handlerCode;

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function setUp(): void
    {
        $saleServiceBuilder = Factory::getServiceBuilder()->getSaleScope();
        $this->cashboxService = $saleServiceBuilder->cashbox();
        $this->cashboxHandlerService = $saleServiceBuilder->cashboxHandler();

        $this->handlerCode = 'batch_cashbox_handler_' . uniqid();
        $this->handlerId = $this->cashboxHandlerService->add(
            $this->handlerCode,
            'Batch cashbox handler',
            [
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
            ]
        )->getId();
    }

    #[\Override]
    protected function tearDown(): void
    {
        $this->cashboxHandlerService->delete($this->handlerId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('Batch::add, Batch::update, Batch::list, Batch::delete cash registers')]
    public function testAddUpdateListDelete(): void
    {
        $items = [];
        for ($i = 1; $i <= self::ITEMS_COUNT; $i++) {
            $items[] = [
                'NAME' => 'Batch cash register ' . $i,
                'REST_CODE' => $this->handlerCode,
                'EMAIL' => 'test@example.com',
                'ACTIVE' => 'Y',
                'SORT' => 100,
                'USE_OFFLINE' => 'N',
                'SETTINGS' => [
                    'AUTH' => ['LOGIN' => 'login'],
                ],
            ];
        }

        $addedIds = [];
        foreach ($this->cashboxService->batch->add($items) as $addedItemResult) {
            $addedIds[] = $addedItemResult->getId();
        }

        $this->assertCount(self::ITEMS_COUNT, $addedIds);

        $updates = [];
        foreach ($addedIds as $id) {
            $updates[$id] = ['NAME' => 'Updated ' . $id, 'SORT' => 200];
        }

        foreach ($this->cashboxService->batch->update($updates) as $updatedItemResult) {
            $this->assertTrue($updatedItemResult->isSuccess());
        }

        $listed = [];
        foreach ($this->cashboxService->batch->list(['ID', 'NAME'], ['@ID' => $addedIds]) as $item) {
            $listed[(int)$item->ID] = $item->NAME;
        }

        $this->assertCount(self::ITEMS_COUNT, $listed);
        foreach ($addedIds as $id) {
            $this->assertSame('Updated ' . $id, $listed[$id]);
        }

        $deletedCount = 0;
        foreach ($this->cashboxService->batch->delete($addedIds) as $deletedItemResult) {
            $this->assertTrue($deletedItemResult->isSuccess());
            $deletedCount++;
        }

        $this->assertSame(self::ITEMS_COUNT, $deletedCount);
    }
}
