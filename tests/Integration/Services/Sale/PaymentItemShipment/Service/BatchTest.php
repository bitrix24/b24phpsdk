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

namespace Bitrix24\SDK\Tests\Integration\Services\Sale\PaymentItemShipment\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Sale\PaymentItemShipment\Service\Batch;
use Bitrix24\SDK\Services\Sale\PaymentItemShipment\Service\PaymentItemShipment;
use Bitrix24\SDK\Services\Sale\SaleServiceBuilder;
use Bitrix24\SDK\Tests\Integration\Factory;
use Bitrix24\SDK\Tests\Integration\Services\Sale\SaleBatchFixturesTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Batch::class)]
#[CoversClass(\Bitrix24\SDK\Services\Sale\PaymentItemShipment\Batch::class)]
class BatchTest extends TestCase
{
    use SaleBatchFixturesTrait;

    private const ITEMS_COUNT = 3;

    private SaleServiceBuilder $saleServiceBuilder;

    private PaymentItemShipment $paymentItemShipmentService;

    private int $personTypeId;

    private int $orderId;

    private int $shipmentId;

    /**
     * @var int[]
     */
    private array $paymentIds = [];

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->saleServiceBuilder = Factory::getServiceBuilder()->getSaleScope();
        $this->paymentItemShipmentService = $this->saleServiceBuilder->paymentItemShipment();

        $paySystemId = $this->getActivePaySystemId();
        $deliveryId = $this->getActiveDeliveryId();
        $this->personTypeId = $this->createPersonType();
        $this->orderId = $this->createOrder($this->personTypeId);
        // payments must be created before the shipment, otherwise the portal binds them to the shipment automatically
        for ($i = 0; $i < self::ITEMS_COUNT; $i++) {
            $this->paymentIds[] = $this->createPayment($this->orderId, $paySystemId);
        }

        $this->shipmentId = $this->createShipment($this->orderId, $deliveryId);
    }

    #[\Override]
    protected function tearDown(): void
    {
        foreach ($this->paymentIds as $paymentId) {
            $this->cleanup(fn () => $this->saleServiceBuilder->payment()->delete($paymentId));
        }

        $this->cleanup(fn () => $this->saleServiceBuilder->shipment()->delete($this->shipmentId));
        $this->cleanup(fn () => $this->saleServiceBuilder->order()->delete($this->orderId));
        $this->cleanup(fn () => $this->saleServiceBuilder->personType()->delete($this->personTypeId));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('Batch::add, Batch::update, Batch::list, Batch::delete payment item shipment bindings')]
    public function testAddUpdateListDelete(): void
    {
        $items = [];
        foreach ($this->paymentIds as $paymentId) {
            $items[] = [
                'paymentId' => $paymentId,
                'shipmentId' => $this->shipmentId,
            ];
        }

        $addedIds = [];
        foreach ($this->paymentItemShipmentService->batch->add($items) as $addedItemResult) {
            $addedIds[] = $addedItemResult->getId();
        }

        $this->assertCount(self::ITEMS_COUNT, $addedIds);

        $updates = [];
        foreach ($addedIds as $id) {
            $updates[$id] = ['xmlId' => 'BATCH_' . $id];
        }

        foreach ($this->paymentItemShipmentService->batch->update($updates) as $updatedItemResult) {
            $this->assertTrue($updatedItemResult->isSuccess());
        }

        $listed = [];
        foreach ($this->paymentItemShipmentService->batch->list(['id', 'xmlId'], ['shipmentId' => $this->shipmentId]) as $item) {
            $listed[(int)$item->id] = $item->xmlId;
        }

        foreach ($addedIds as $id) {
            $this->assertArrayHasKey($id, $listed);
            $this->assertSame('BATCH_' . $id, $listed[$id]);
        }

        $deletedCount = 0;
        foreach ($this->paymentItemShipmentService->batch->delete($addedIds) as $deletedItemResult) {
            $this->assertTrue($deletedItemResult->isSuccess());
            $deletedCount++;
        }

        $this->assertSame(self::ITEMS_COUNT, $deletedCount);
    }
}
