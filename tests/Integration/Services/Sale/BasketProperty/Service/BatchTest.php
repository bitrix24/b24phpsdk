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

namespace Bitrix24\SDK\Tests\Integration\Services\Sale\BasketProperty\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Sale\BasketProperty\Service\BasketProperty;
use Bitrix24\SDK\Services\Sale\BasketProperty\Service\Batch;
use Bitrix24\SDK\Services\Sale\SaleServiceBuilder;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Batch::class)]
#[CoversClass(\Bitrix24\SDK\Services\Sale\BasketProperty\Batch::class)]
class BatchTest extends TestCase
{
    private const ITEMS_COUNT = 60;

    private SaleServiceBuilder $saleServiceBuilder;

    private BasketProperty $basketPropertyService;

    private int $personTypeId;

    private int $orderId;

    private int $basketItemId;

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->saleServiceBuilder = Fabric::getServiceBuilder()->getSaleScope();
        $this->basketPropertyService = $this->saleServiceBuilder->basketProperty();

        $this->personTypeId = $this->saleServiceBuilder->personType()->add([
            'name' => 'Test Person Type for batch ' . uniqid(),
            'sort' => 100,
        ])->getId();
        $this->orderId = $this->saleServiceBuilder->order()->add([
            'lid' => 's1',
            'personTypeId' => $this->personTypeId,
            'currency' => 'USD',
            'price' => 100.00,
        ])->getId();
        $this->basketItemId = $this->saleServiceBuilder->basketItem()->add([
            'orderId' => $this->orderId,
            'quantity' => 1,
            'price' => 100.00,
            'currency' => 'USD',
            'name' => 'Test Product ' . uniqid(),
            'productId' => 0,
        ])->getId();
    }

    #[\Override]
    protected function tearDown(): void
    {
        $this->saleServiceBuilder->basketItem()->delete($this->basketItemId);
        $this->saleServiceBuilder->order()->delete($this->orderId);
        $this->saleServiceBuilder->personType()->delete($this->personTypeId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('Batch::add, Batch::update, Batch::list, Batch::delete basket properties')]
    public function testAddUpdateListDelete(): void
    {
        $items = [];
        for ($i = 1; $i <= self::ITEMS_COUNT; $i++) {
            $items[] = [
                'basketId' => $this->basketItemId,
                'name' => 'Batch property ' . $i,
                'value' => 'Value ' . $i,
                'code' => 'BATCH_' . $i,
            ];
        }

        $addedIds = [];
        foreach ($this->basketPropertyService->batch->add($items) as $addedItemResult) {
            $addedIds[] = $addedItemResult->getId();
        }

        $this->assertCount(self::ITEMS_COUNT, $addedIds);

        $updates = [];
        foreach ($addedIds as $id) {
            $updates[$id] = ['name' => 'Batch property ' . $id, 'code' => 'BATCH_' . $id, 'value' => 'Updated ' . $id];
        }

        foreach ($this->basketPropertyService->batch->update($updates) as $updatedItemResult) {
            $this->assertTrue($updatedItemResult->isSuccess());
        }

        $listed = [];
        foreach ($this->basketPropertyService->batch->list(['id', 'value'], ['basketId' => $this->basketItemId]) as $item) {
            $listed[(int)$item->id] = $item->value;
        }

        $this->assertCount(self::ITEMS_COUNT, $listed);
        foreach ($addedIds as $addedId) {
            $this->assertSame('Updated ' . $addedId, $listed[$addedId]);
        }

        $deletedCount = 0;
        foreach ($this->basketPropertyService->batch->delete($addedIds) as $deletedItemResult) {
            $this->assertTrue($deletedItemResult->isSuccess());
            $deletedCount++;
        }

        $this->assertSame(self::ITEMS_COUNT, $deletedCount);
    }
}
