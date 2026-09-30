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

namespace Bitrix24\SDK\Tests\Integration\Services\Sale\Delivery\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Sale\Delivery\Service\Batch;
use Bitrix24\SDK\Services\Sale\Delivery\Service\Delivery;
use Bitrix24\SDK\Services\Sale\SaleServiceBuilder;
use Bitrix24\SDK\Tests\Integration\Factory;
use Bitrix24\SDK\Tests\Integration\Services\Sale\SaleBatchFixturesTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Batch::class)]
#[CoversClass(\Bitrix24\SDK\Services\Sale\Delivery\Batch::class)]
class BatchTest extends TestCase
{
    use SaleBatchFixturesTrait;

    private const ITEMS_COUNT = 3;

    private SaleServiceBuilder $saleServiceBuilder;

    private Delivery $deliveryService;

    private int $handlerId;

    private string $handlerCode;

    /**
     * @var int[]
     */
    private array $deliveryIds = [];

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->saleServiceBuilder = Factory::getServiceBuilder()->getSaleScope();
        $this->deliveryService = $this->saleServiceBuilder->delivery();
        $this->handlerId = $this->createDeliveryHandler('batch_delivery_handler_' . uniqid());
        $this->handlerCode = $this->getDeliveryHandlerCode($this->handlerId);
    }

    #[\Override]
    protected function tearDown(): void
    {
        foreach ($this->deliveryIds as $deliveryId) {
            $this->cleanup(fn () => $this->saleServiceBuilder->delivery()->delete($deliveryId));
        }

        $this->cleanup(fn () => $this->saleServiceBuilder->deliveryHandler()->delete($this->handlerId));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('Batch::add, Batch::update, Batch::configUpdate, Batch::getlist, Batch::delete delivery services')]
    public function testAddUpdateListDelete(): void
    {
        $items = [];
        for ($i = 1; $i <= self::ITEMS_COUNT; $i++) {
            $items[] = $this->getDeliveryFields($this->handlerCode);
        }

        $addedIds = [];
        foreach ($this->deliveryService->batch->add($items) as $addedItemResult) {
            $this->assertSame('Test Delivery for batch', $addedItemResult->getParent()->NAME);
            $addedIds[] = $addedItemResult->getId();
        }

        $this->deliveryIds = $addedIds;
        $this->assertCount(self::ITEMS_COUNT, $addedIds);

        $updates = [];
        $configs = [];
        foreach ($addedIds as $id) {
            $updates[$id] = ['NAME' => 'Updated ' . $id];
            $configs[$id] = [['CODE' => 'API_KEY', 'VALUE' => 'updated_key_' . $id]];
        }

        foreach ($this->deliveryService->batch->update($updates) as $updatedItemResult) {
            $this->assertTrue($updatedItemResult->isSuccess());
        }

        foreach ($this->deliveryService->batch->configUpdate($configs) as $updatedItemResult) {
            $this->assertTrue($updatedItemResult->isSuccess());
        }

        foreach ($addedIds as $id) {
            $config = $this->deliveryService->configGet($id)->getConfig();
            $this->assertContains(['CODE' => 'API_KEY', 'VALUE' => 'updated_key_' . $id], $config);
        }

        $listed = [];
        foreach ($this->deliveryService->batch->getlist(['ID', 'NAME'], ['@ID' => $addedIds]) as $item) {
            $listed[(int)$item->ID] = $item->NAME;
        }

        $this->assertCount(self::ITEMS_COUNT, $listed);
        foreach ($addedIds as $id) {
            $this->assertSame('Updated ' . $id, $listed[$id]);
        }

        $deletedCount = 0;
        foreach ($this->deliveryService->batch->delete($addedIds) as $deletedItemResult) {
            $this->assertTrue($deletedItemResult->isSuccess());
            $deletedCount++;
        }

        $this->assertSame(self::ITEMS_COUNT, $deletedCount);
        $this->deliveryIds = [];
    }
}
