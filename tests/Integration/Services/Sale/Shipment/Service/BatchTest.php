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

namespace Bitrix24\SDK\Tests\Integration\Services\Sale\Shipment\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Sale\SaleServiceBuilder;
use Bitrix24\SDK\Services\Sale\Shipment\Service\Batch;
use Bitrix24\SDK\Services\Sale\Shipment\Service\Shipment;
use Bitrix24\SDK\Tests\Integration\Factory;
use Bitrix24\SDK\Tests\Integration\Services\Sale\SaleBatchFixturesTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Batch::class)]
#[CoversClass(\Bitrix24\SDK\Services\Sale\Shipment\Batch::class)]
class BatchTest extends TestCase
{
    use SaleBatchFixturesTrait;

    private const ITEMS_COUNT = 3;

    private SaleServiceBuilder $saleServiceBuilder;

    private Shipment $shipmentService;

    private int $personTypeId;

    private int $orderId;

    private int $deliveryId;

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->saleServiceBuilder = Factory::getServiceBuilder()->getSaleScope();
        $this->shipmentService = $this->saleServiceBuilder->shipment();
        $this->deliveryId = $this->getActiveDeliveryId();
        $this->personTypeId = $this->createPersonType();
        $this->orderId = $this->createOrder($this->personTypeId);
    }

    #[\Override]
    protected function tearDown(): void
    {
        $this->cleanup(fn () => $this->saleServiceBuilder->order()->delete($this->orderId));
        $this->cleanup(fn () => $this->saleServiceBuilder->personType()->delete($this->personTypeId));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('Batch::add, Batch::update, Batch::list, Batch::delete shipments')]
    public function testAddUpdateListDelete(): void
    {
        $items = [];
        for ($i = 1; $i <= self::ITEMS_COUNT; $i++) {
            $items[] = [
                'orderId' => $this->orderId,
                'deliveryId' => $this->deliveryId,
                'allowDelivery' => 'Y',
                'deducted' => 'N',
            ];
        }

        $addedIds = [];
        foreach ($this->shipmentService->batch->add($items) as $addedItemResult) {
            $addedIds[] = $addedItemResult->getId();
        }

        $this->assertCount(self::ITEMS_COUNT, $addedIds);

        $updates = [];
        foreach ($addedIds as $id) {
            $updates[$id] = [
                'deliveryId' => $this->deliveryId,
                'allowDelivery' => 'Y',
                'deducted' => 'N',
                'trackingNumber' => 'BATCH_' . $id,
            ];
        }

        foreach ($this->shipmentService->batch->update($updates) as $updatedItemResult) {
            $this->assertTrue($updatedItemResult->isSuccess());
        }

        $listed = [];
        foreach ($this->shipmentService->batch->list(['id', 'trackingNumber'], ['orderId' => $this->orderId]) as $item) {
            $listed[(int)$item->id] = $item->trackingNumber;
        }

        foreach ($addedIds as $id) {
            $this->assertArrayHasKey($id, $listed);
            $this->assertSame('BATCH_' . $id, $listed[$id]);
        }

        $deletedCount = 0;
        foreach ($this->shipmentService->batch->delete($addedIds) as $deletedItemResult) {
            $this->assertTrue($deletedItemResult->isSuccess());
            $deletedCount++;
        }

        $this->assertSame(self::ITEMS_COUNT, $deletedCount);
    }
}
