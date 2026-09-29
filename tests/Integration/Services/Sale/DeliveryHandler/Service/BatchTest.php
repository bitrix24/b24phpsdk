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

namespace Bitrix24\SDK\Tests\Integration\Services\Sale\DeliveryHandler\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Sale\DeliveryHandler\Service\Batch;
use Bitrix24\SDK\Services\Sale\DeliveryHandler\Service\DeliveryHandler;
use Bitrix24\SDK\Tests\Integration\Fabric;
use Bitrix24\SDK\Tests\Integration\Services\Sale\SaleBatchFixturesTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Batch::class)]
#[CoversClass(\Bitrix24\SDK\Services\Sale\DeliveryHandler\Batch::class)]
class BatchTest extends TestCase
{
    use SaleBatchFixturesTrait;

    private const ITEMS_COUNT = 3;

    private DeliveryHandler $deliveryHandlerService;

    #[\Override]
    protected function setUp(): void
    {
        $this->deliveryHandlerService = Fabric::getServiceBuilder()->getSaleScope()->deliveryHandler();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('Batch::add, Batch::update, Batch::list, Batch::delete delivery service handlers')]
    public function testAddUpdateListDelete(): void
    {
        $codePrefix = 'batch_delivery_handler_' . uniqid() . '_';
        $items = [];
        for ($i = 1; $i <= self::ITEMS_COUNT; $i++) {
            $items[] = $this->getDeliveryHandlerFields($codePrefix . $i);
        }

        $addedIds = [];
        foreach ($this->deliveryHandlerService->batch->add($items) as $addedItemResult) {
            $addedIds[] = $addedItemResult->getId();
        }

        $this->assertCount(self::ITEMS_COUNT, $addedIds);

        $updates = [];
        foreach ($addedIds as $id) {
            $updates[$id] = ['NAME' => 'Updated ' . $id, 'SORT' => 200];
        }

        foreach ($this->deliveryHandlerService->batch->update($updates) as $updatedItemResult) {
            $this->assertTrue($updatedItemResult->isSuccess());
        }

        $listed = [];
        foreach ($this->deliveryHandlerService->batch->list() as $item) {
            $listed[(int)$item->ID] = $item->NAME;
        }

        foreach ($addedIds as $addedId) {
            $this->assertArrayHasKey($addedId, $listed);
            $this->assertSame('Updated ' . $addedId, $listed[$addedId]);
        }

        $deletedCount = 0;
        foreach ($this->deliveryHandlerService->batch->delete($addedIds) as $deletedItemResult) {
            $this->assertTrue($deletedItemResult->isSuccess());
            $deletedCount++;
        }

        $this->assertSame(self::ITEMS_COUNT, $deletedCount);
    }
}
