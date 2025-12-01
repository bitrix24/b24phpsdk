<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Sally Fancen <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\Sale\Shipment\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Sale\Shipment\Service\Shipment;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

/**
 * Class ShipmentTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Sale\Shipment\Service
 */
#[CoversMethod(Shipment::class,'add')]
#[CoversMethod(Shipment::class,'update')]
#[CoversMethod(Shipment::class,'get')]
#[CoversMethod(Shipment::class,'list')]
#[CoversMethod(Shipment::class,'delete')]
#[CoversMethod(Shipment::class,'getFields')]
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\Sale\Shipment\Service\Shipment::class)]
class ShipmentTest extends TestCase
{
    protected Shipment $shipmentService;

    protected int $orderId;

    protected int $personTypeId;

    protected int $deliveryId;

    /**
     * Set up test environment
     */
    protected function setUp(): void
    {
        $this->shipmentService = Factory::getServiceBuilder()->getSaleScope()->shipment();
        $this->personTypeId = $this->createPersonType();
        $this->orderId = $this->createOrder();
        $this->deliveryId = $this->getDeliveryId();
    }

    /**
     * Clean up resources after tests
     */
    protected function tearDown(): void
    {
        // Clean up created resources
        if (isset($this->orderId)) {
            $this->deleteOrder($this->orderId);
        }

        if (isset($this->personTypeId)) {
            $this->deletePersonType($this->personTypeId);
        }
    }

    /**
     * Helper method to create a person type for testing
     */
    protected function createPersonType(): int
    {
        $personTypeService = Factory::getServiceBuilder()->getSaleScope()->personType();
        $addedPersonTypeResult = $personTypeService->add([
            'name' => 'Test Person Type for Shipment',
            'sort' => 100,
            'active' => 'Y'
        ]);
        return $addedPersonTypeResult->getId();
    }

    /**
     * Helper method to delete a person type after testing
     */
    protected function deletePersonType(int $id): void
    {
        $personTypeService = Factory::getServiceBuilder()->getSaleScope()->personType();
        $personTypeService->delete($id);
    }

    /**
     * Helper method to create an order for testing
     */
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

    /**
     * Helper method to delete an order after testing
     */
    protected function deleteOrder(int $id): void
    {
        $orderService = Factory::getServiceBuilder()->getSaleScope()->order();
        $orderService->delete($id);
    }

    /**
     * Helper method to get a delivery service ID
     * 
     * Uses sale.delivery.getList API method to get the first active delivery service
     * @link https://apidocs.bitrix24.com/api-reference/sale/delivery/delivery/sale-delivery-get-list.html
     * 
     * @throws BaseException
     * @throws TransportException
     */
    protected function getDeliveryId(): int
    {
        $core = Factory::getCore();
        $response = $core->call('sale.delivery.getList', [
            'filter' => [
                'ACTIVE' => 'Y'
            ],
            'select' => ['ID'],
            'order' => ['SORT' => 'ASC'],
        ]);

        $deliveries = $response->getResponseData()->getResult();

        if ($deliveries === []) {
            $this->markTestSkipped('No active delivery services found in the portal. Test cannot be executed.');
        }

        return (int)$deliveries[0]['ID'];
    }

    /**
     * Test adding a shipment
     * 
     * @throws BaseException
     * @throws TransportException
     */
    public function testAdd(): void
    {
        // Create a shipment
        $shipmentFields = [
            'orderId' => $this->orderId,
            'deliveryId' => $this->deliveryId,
            'allowDelivery' => 'Y',
            'deducted' => 'N',
        ];

        $addedShipmentResult = $this->shipmentService->add($shipmentFields);
        $shipmentId = $addedShipmentResult->getId();

        self::assertGreaterThan(0, $shipmentId);

        // Clean up
        $this->shipmentService->delete($shipmentId);
    }

    /**
     * Test updating a shipment
     * 
     * @throws BaseException
     * @throws TransportException
     */
    public function testUpdate(): void
    {
        // Create a shipment
        $shipmentFields = [
            'orderId' => $this->orderId,
            'deliveryId' => $this->deliveryId,
            'allowDelivery' => 'Y',
            'deducted' => 'N',
        ];

        $addedShipmentResult = $this->shipmentService->add($shipmentFields);
        $shipmentId = $addedShipmentResult->getId();

        // Update the shipment
        $updateFields = [
            'trackingNumber' => 'TEST123456789',
            'deliveryId' => $this->deliveryId,
            'allowDelivery' => 'Y',
            'deducted' => 'N',
        ];

        $updatedShipmentResult = $this->shipmentService->update($shipmentId, $updateFields);
        self::assertTrue($updatedShipmentResult->isSuccess());

        // Verify the update
        $shipmentResult = $this->shipmentService->get($shipmentId);
        $shipment = $shipmentResult->shipment();

        self::assertEquals('TEST123456789', $shipment->trackingNumber);

        // Clean up
        $this->shipmentService->delete($shipmentId);
    }

    /**
     * Test getting a shipment
     * 
     * @throws BaseException
     * @throws TransportException
     */
    public function testGet(): void
    {
        // Create a shipment
        $shipmentFields = [
            'orderId' => $this->orderId,
            'deliveryId' => $this->deliveryId,
            'allowDelivery' => 'Y',
            'deducted' => 'N',
        ];

        $addedShipmentResult = $this->shipmentService->add($shipmentFields);
        $shipmentId = $addedShipmentResult->getId();

        // Get the shipment
        $shipmentResult = $this->shipmentService->get($shipmentId);
        $shipment = $shipmentResult->shipment();

        self::assertEquals($shipmentId, $shipment->id);
        self::assertEquals($this->orderId, $shipment->orderId);
        self::assertEquals($this->deliveryId, $shipment->deliveryId);

        // Clean up
        $this->shipmentService->delete($shipmentId);
    }

    /**
     * Test listing shipments
     * 
     * @throws BaseException
     * @throws TransportException
     */
    public function testList(): void
    {
        // Create a shipment
        $shipmentFields = [
            'orderId' => $this->orderId,
            'deliveryId' => $this->deliveryId,
            'allowDelivery' => 'Y',
            'deducted' => 'N',
        ];

        $addedShipmentResult = $this->shipmentService->add($shipmentFields);
        $shipmentId = $addedShipmentResult->getId();

        // List shipments with filter
        $filter = [
            'orderId' => $this->orderId,
        ];

        $shipmentsResult = $this->shipmentService->list([], $filter);
        $shipments = $shipmentsResult->getShipments(); // Используем текущий метод в ShipmentsResult

        self::assertGreaterThanOrEqual(1, count($shipments));

        // Verify our shipment is in the list
        $found = false;
        foreach ($shipments as $shipment) {
            if ($shipment->id === $shipmentId) {
                $found = true;
                break;
            }
        }

        self::assertTrue($found);

        // Clean up
        $this->shipmentService->delete($shipmentId);
    }

    /**
     * Test deleting a shipment
     * 
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelete(): void
    {
        // Create a shipment
        $shipmentFields = [
            'orderId' => $this->orderId,
            'deliveryId' => $this->deliveryId,
            'allowDelivery' => 'Y',
            'deducted' => 'N',
        ];

        $addedShipmentResult = $this->shipmentService->add($shipmentFields);
        $shipmentId = $addedShipmentResult->getId();

        // Delete the shipment
        $deletedItemResult = $this->shipmentService->delete($shipmentId);
        self::assertTrue($deletedItemResult->isSuccess());

        // Verify deletion
        $this->expectException(BaseException::class);
        $this->shipmentService->get($shipmentId);
    }

    /**
     * Test getting shipment fields
     * 
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetFields(): void
    {
        $shipmentFieldsResult = $this->shipmentService->getFields();
        $fields = $shipmentFieldsResult->getFieldsDescription();

        self::assertIsArray($fields);
        self::assertNotEmpty($fields);
    }
}
