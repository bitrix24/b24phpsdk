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

namespace Bitrix24\SDK\Tests\Integration\Services\Sale\DeliveryExtraService\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Sale\DeliveryExtraService\Service\Batch;
use Bitrix24\SDK\Services\Sale\DeliveryExtraService\Service\DeliveryExtraService;
use Bitrix24\SDK\Services\Sale\SaleServiceBuilder;
use Bitrix24\SDK\Tests\Integration\Fabric;
use Bitrix24\SDK\Tests\Integration\Services\Sale\SaleBatchFixturesTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Batch::class)]
#[CoversClass(\Bitrix24\SDK\Services\Sale\DeliveryExtraService\Batch::class)]
class BatchTest extends TestCase
{
    use SaleBatchFixturesTrait;

    private const ITEMS_COUNT = 3;

    private SaleServiceBuilder $saleServiceBuilder;

    private DeliveryExtraService $deliveryExtraService;

    private int $handlerId;

    private int $deliveryId;

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->saleServiceBuilder = Fabric::getServiceBuilder()->getSaleScope();
        $this->deliveryExtraService = $this->saleServiceBuilder->deliveryExtraService();
        $this->handlerId = $this->createDeliveryHandler('batch_delivery_handler_' . uniqid());
        $this->deliveryId = $this->createDelivery($this->getDeliveryHandlerCode($this->handlerId));
    }

    #[\Override]
    protected function tearDown(): void
    {
        $this->cleanup(fn (): \Bitrix24\SDK\Core\Result\DeletedItemResult => $this->saleServiceBuilder->delivery()->delete($this->deliveryId));
        $this->cleanup(fn (): \Bitrix24\SDK\Core\Result\DeletedItemResult => $this->saleServiceBuilder->deliveryHandler()->delete($this->handlerId));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('Batch::add, Batch::update, Batch::delete delivery extra services')]
    public function testAddUpdateDelete(): void
    {
        $items = [];
        for ($i = 1; $i <= self::ITEMS_COUNT; $i++) {
            $items[] = [
                'DELIVERY_ID' => $this->deliveryId,
                'TYPE' => 'checkbox',
                'NAME' => 'Batch extra service ' . $i,
                'ACTIVE' => 'Y',
                'CODE' => 'batch_extra_service_' . $i,
                'SORT' => 100 + $i,
                'PRICE' => 10.5,
            ];
        }

        $addedIds = [];
        foreach ($this->deliveryExtraService->batch->add($items) as $addedItemResult) {
            $addedIds[] = $addedItemResult->getId();
        }

        $this->assertCount(self::ITEMS_COUNT, $addedIds);

        $updates = [];
        foreach ($addedIds as $id) {
            $updates[$id] = ['NAME' => 'Updated ' . $id, 'PRICE' => 20.5];
        }

        foreach ($this->deliveryExtraService->batch->update($updates) as $updatedItemResult) {
            $this->assertTrue($updatedItemResult->isSuccess());
        }

        $listed = [];
        foreach ($this->deliveryExtraService->get($this->deliveryId)->getDeliveryExtraServices() as $deliveryExtraServiceItemResult) {
            $listed[(int)$deliveryExtraServiceItemResult->ID] = $deliveryExtraServiceItemResult->NAME;
        }

        foreach ($addedIds as $addedId) {
            $this->assertArrayHasKey($addedId, $listed);
            $this->assertSame('Updated ' . $addedId, $listed[$addedId]);
        }

        $deletedCount = 0;
        foreach ($this->deliveryExtraService->batch->delete($addedIds) as $deletedItemResult) {
            $this->assertTrue($deletedItemResult->isSuccess());
            $deletedCount++;
        }

        $this->assertSame(self::ITEMS_COUNT, $deletedCount);
    }
}
