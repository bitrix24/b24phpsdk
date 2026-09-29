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

namespace Bitrix24\SDK\Tests\Integration\Services\Sale\ShipmentItem\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Sale\SaleServiceBuilder;
use Bitrix24\SDK\Services\Sale\ShipmentItem\Service\Batch;
use Bitrix24\SDK\Services\Sale\ShipmentItem\Service\ShipmentItem;
use Bitrix24\SDK\Tests\Integration\Fabric;
use Bitrix24\SDK\Tests\Integration\Services\Sale\SaleBatchFixturesTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Batch::class)]
#[CoversClass(\Bitrix24\SDK\Services\Sale\ShipmentItem\Batch::class)]
class BatchTest extends TestCase
{
    use SaleBatchFixturesTrait;

    private const ITEMS_COUNT = 3;

    private SaleServiceBuilder $saleServiceBuilder;

    private ShipmentItem $shipmentItemService;

    private int $personTypeId;

    private int $orderId;

    private int $shipmentId;

    /**
     * @var int[]
     */
    private array $basketItemIds = [];

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->saleServiceBuilder = Fabric::getServiceBuilder()->getSaleScope();
        $this->shipmentItemService = $this->saleServiceBuilder->shipmentItem();
        $deliveryId = $this->getActiveDeliveryId();
        $this->personTypeId = $this->createPersonType();
        $this->orderId = $this->createOrder($this->personTypeId);
        $this->shipmentId = $this->createShipment($this->orderId, $deliveryId);
        for ($i = 0; $i < self::ITEMS_COUNT; $i++) {
            $this->basketItemIds[] = $this->createBasketItem($this->orderId);
        }
    }

    #[\Override]
    protected function tearDown(): void
    {
        $this->cleanup(fn (): \Bitrix24\SDK\Core\Result\DeletedItemResult => $this->saleServiceBuilder->shipment()->delete($this->shipmentId));
        foreach ($this->basketItemIds as $basketItemId) {
            $this->cleanup(fn (): \Bitrix24\SDK\Core\Result\DeletedItemResult => $this->saleServiceBuilder->basketItem()->delete($basketItemId));
        }

        $this->cleanup(fn (): \Bitrix24\SDK\Core\Result\DeletedItemResult => $this->saleServiceBuilder->order()->delete($this->orderId));
        $this->cleanup(fn (): \Bitrix24\SDK\Core\Result\DeletedItemResult => $this->saleServiceBuilder->personType()->delete($this->personTypeId));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('Batch::add, Batch::update, Batch::list, Batch::delete shipment items')]
    public function testAddUpdateListDelete(): void
    {
        $items = [];
        foreach ($this->basketItemIds as $basketItemId) {
            $items[] = [
                'orderDeliveryId' => $this->shipmentId,
                'basketId' => $basketItemId,
                'quantity' => 1,
            ];
        }

        $addedIds = [];
        foreach ($this->shipmentItemService->batch->add($items) as $addedItemResult) {
            $addedIds[] = $addedItemResult->getId();
        }

        $this->assertCount(self::ITEMS_COUNT, $addedIds);

        $updates = [];
        foreach ($addedIds as $id) {
            $updates[$id] = ['quantity' => 2];
        }

        foreach ($this->shipmentItemService->batch->update($updates) as $updatedItemResult) {
            $this->assertTrue($updatedItemResult->isSuccess());
        }

        $listed = [];
        foreach ($this->shipmentItemService->batch->list(['id', 'quantity'], ['orderDeliveryId' => $this->shipmentId]) as $item) {
            $listed[(int)$item->id] = (float)$item->quantity;
        }

        foreach ($addedIds as $addedId) {
            $this->assertArrayHasKey($addedId, $listed);
            $this->assertSame(2.0, $listed[$addedId]);
        }

        $deletedCount = 0;
        foreach ($this->shipmentItemService->batch->delete($addedIds) as $deletedItemResult) {
            $this->assertTrue($deletedItemResult->isSuccess());
            $deletedCount++;
        }

        $this->assertSame(self::ITEMS_COUNT, $deletedCount);
    }
}
