<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\Sale\ShipmentItem\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core;
use Bitrix24\SDK\Services\Sale\Shipment\Service\Shipment;
use Bitrix24\SDK\Services\Sale\ShipmentItem\Result\ShipmentItemItemResult;
use Bitrix24\SDK\Services\Sale\ShipmentItem\Service\ShipmentItem;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversMethod(ShipmentItem::class,'add')]
#[CoversMethod(ShipmentItem::class,'get')]
#[CoversMethod(ShipmentItem::class,'list')]
#[CoversMethod(ShipmentItem::class,'update')]
#[CoversMethod(ShipmentItem::class,'delete')]
#[CoversMethod(ShipmentItem::class,'getFields')]
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\Sale\ShipmentItem\Service\ShipmentItem::class)]
class ShipmentItemTest extends TestCase
{
    use CustomBitrix24Assertions;

    protected ShipmentItem $shipmentItemService;

    protected Shipment $shipmentService;

    protected int $personTypeId;

    protected int $orderId;

    protected int $deliveryId;

    protected int $shipmentId;

    protected int $basketId;

    #[\Override]
    protected function setUp(): void
    {
        $saleServiceBuilder = Factory::getServiceBuilder()->getSaleScope();
        $this->shipmentItemService = $saleServiceBuilder->shipmentItem();
        $this->shipmentService = $saleServiceBuilder->shipment();

        $this->personTypeId = $this->createPersonType();
        $this->orderId = $this->createOrder();
        $this->deliveryId = $this->getDeliveryId();
        $this->shipmentId = $this->createShipment();
        $this->basketId = $this->createBasketItem();
    }

    #[\Override]
    protected function tearDown(): void
    {
        // Clean-up in reverse order
        if (isset($this->basketId)) {
            try { $this->deleteBasketItem($this->basketId); } catch (\Throwable) {}
        }

        if (isset($this->shipmentId)) {
            try { $this->shipmentService->delete($this->shipmentId); } catch (\Throwable) {}
        }

        if (isset($this->orderId)) {
            try { $this->deleteOrder($this->orderId); } catch (\Throwable) {}
        }

        if (isset($this->personTypeId)) {
            try { $this->deletePersonType($this->personTypeId); } catch (\Throwable) {}
        }
    }

    protected function createPersonType(): int
    {
        $personTypeService = Factory::getServiceBuilder()->getSaleScope()->personType();
        $addedPersonTypeResult = $personTypeService->add([
            'name' => 'Test Person Type for ShipmentItem',
            'sort' => 100,
            'active' => 'Y'
        ]);
        return $addedPersonTypeResult->getId();
    }

    protected function deletePersonType(int $id): void
    {
        $personTypeService = Factory::getServiceBuilder()->getSaleScope()->personType();
        $personTypeService->delete($id);
    }

    protected function createOrder(): int
    {
        $orderService = Factory::getServiceBuilder()->getSaleScope()->order();
        $orderAddedResult = $orderService->add([
            'personTypeId' => $this->personTypeId,
            'userEmail' => 'test@example.com',
            'currency' => 'RUB',
            'lid' => 's1',
        ]);
        return $orderAddedResult->getId();
    }

    protected function deleteOrder(int $id): void
    {
        $orderService = Factory::getServiceBuilder()->getSaleScope()->order();
        $orderService->delete($id);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    protected function getDeliveryId(): int
    {
        $core = Factory::getCore();
        $response = $core->call('sale.delivery.getList', [
            'filter' => ['ACTIVE' => 'Y'],
            'select' => ['ID'],
            'order' => ['SORT' => 'ASC'],
        ]);
        $deliveries = $response->getResponseData()->getResult();
        if ($deliveries === []) {
            $this->markTestSkipped('No active delivery services found in the portal.');
        }

        return (int)$deliveries[0]['ID'];
    }

    protected function createShipment(): int
    {
        $addedShipmentResult = $this->shipmentService->add([
            'orderId' => $this->orderId,
            'deliveryId' => $this->deliveryId,
            'allowDelivery' => 'Y',
            'deducted' => 'N',
        ]);
        return $addedShipmentResult->getId();
    }

    protected function createBasketItem(): int
    {
        $basketItem = Factory::getServiceBuilder()->getSaleScope()->basketItem();
        $addedBasketItemResult = $basketItem->add([
            'orderId' => $this->orderId,
            'productId' => 0, // there is no product from the catalog
            'quantity' => 2,
            'price' => 100.00,
            'currency' => 'RUB',
            'name' => 'Test Product for ShipmentItem',
        ]);
        return $addedBasketItemResult->getId();
    }

    protected function deleteBasketItem(int $id): void
    {
        $basketItem = Factory::getServiceBuilder()->getSaleScope()->basketItem();
        $basketItem->delete($id);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAdd(): void
    {
        $addedShipmentItemResult = $this->shipmentItemService->add([
            'orderDeliveryId' => $this->shipmentId,
            'basketId' => $this->basketId,
            'quantity' => 1,
        ]);

        self::assertGreaterThan(0, $addedShipmentItemResult->getId());

        // Clean up the added item
        $this->shipmentItemService->delete($addedShipmentItemResult->getId());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGet(): void
    {
        $addedShipmentItemResult = $this->shipmentItemService->add([
            'orderDeliveryId' => $this->shipmentId,
            'basketId' => $this->basketId,
            'quantity' => 1,
        ]);

        $shipmentItemItemResult = $this->shipmentItemService->get($addedShipmentItemResult->getId())->shipmentItem();
        self::assertEquals($addedShipmentItemResult->getId(), $shipmentItemItemResult->id);
        self::assertEquals($this->shipmentId, $shipmentItemItemResult->orderDeliveryId);
        self::assertEquals($this->basketId, $shipmentItemItemResult->basketId);

        // Clean up
        $this->shipmentItemService->delete($addedShipmentItemResult->getId());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testList(): void
    {
        $addedShipmentItemResult = $this->shipmentItemService->add([
            'orderDeliveryId' => $this->shipmentId,
            'basketId' => $this->basketId,
            'quantity' => 1,
        ]);

        $list = $this->shipmentItemService->list(
            ['id', 'orderDeliveryId', 'basketId', 'quantity'],
            ['orderDeliveryId' => $this->shipmentId]
        );

        $items = $list->getShipmentItems();
        self::assertNotEmpty($items);
        $found = false;
        foreach ($items as $item) {
            if ($item->id === $addedShipmentItemResult->getId()) {
                $found = true;
                self::assertEquals($this->shipmentId, $item->orderDeliveryId);
                break;
            }
        }

        self::assertTrue($found, 'Added shipment item not found in list');

        // Clean up
        $this->shipmentItemService->delete($addedShipmentItemResult->getId());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testUpdate(): void
    {
        $addedShipmentItemResult = $this->shipmentItemService->add([
            'orderDeliveryId' => $this->shipmentId,
            'basketId' => $this->basketId,
            'quantity' => 1,
        ]);

        $updatedShipmentItemResult = $this->shipmentItemService->update($addedShipmentItemResult->getId(), [
            'quantity' => 2,
        ]);

        self::assertTrue($updatedShipmentItemResult->isSuccess());

        $shipmentItemItemResult = $this->shipmentItemService->get($addedShipmentItemResult->getId())->shipmentItem();
        self::assertEquals(2.0, $shipmentItemItemResult->quantity);

        // Clean up
        $this->shipmentItemService->delete($addedShipmentItemResult->getId());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelete(): void
    {
        $addedShipmentItemResult = $this->shipmentItemService->add([
            'orderDeliveryId' => $this->shipmentId,
            'basketId' => $this->basketId,
            'quantity' => 1,
        ]);

        $deletedItemResult = $this->shipmentItemService->delete($addedShipmentItemResult->getId());
        self::assertTrue($deletedItemResult->isSuccess());

        // Verify it's deleted by trying to get it (should throw exception or return empty)
        $this->expectException(BaseException::class);
        $this->shipmentItemService->get($addedShipmentItemResult->getId());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetFields(): void
    {
        $fields = $this->shipmentItemService->getFields()->getFieldsDescription();
        self::assertIsArray($fields);
        self::assertNotEmpty($fields);
    }

    public function testAllSystemFieldsAnnotated(): void
    {
        $propListFromApi = (new Core\Fields\FieldsFilter())->filterSystemFields(array_keys($this->shipmentItemService->getFields()->getFieldsDescription()));
        $this->assertBitrix24AllResultItemFieldsAnnotated($propListFromApi, ShipmentItemItemResult::class);
    }

    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $allFields = $this->shipmentItemService->getFields()->getFieldsDescription();
        $systemFieldsCodes = (new Core\Fields\FieldsFilter())->filterSystemFields(array_keys($allFields));
        $systemFields = array_filter($allFields, static fn($code): bool => in_array($code, $systemFieldsCodes, true), ARRAY_FILTER_USE_KEY);

        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation(
            $systemFields,
            ShipmentItemItemResult::class);
    }
}