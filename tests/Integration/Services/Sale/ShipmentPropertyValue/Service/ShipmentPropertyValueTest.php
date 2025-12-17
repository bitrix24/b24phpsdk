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

namespace Bitrix24\SDK\Tests\Integration\Services\Sale\ShipmentPropertyValue\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Sale\Shipment\Service\Shipment;
use Bitrix24\SDK\Services\Sale\ShipmentProperty\Service\ShipmentProperty;
use Bitrix24\SDK\Services\Sale\ShipmentPropertyValue\Service\ShipmentPropertyValue;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversMethod(ShipmentPropertyValue::class,'modify')]
#[CoversMethod(ShipmentPropertyValue::class,'get')]
#[CoversMethod(ShipmentPropertyValue::class,'list')]
#[CoversMethod(ShipmentPropertyValue::class,'delete')]
#[CoversMethod(ShipmentPropertyValue::class,'getFields')]
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\Sale\ShipmentPropertyValue\Service\ShipmentPropertyValue::class)]
class ShipmentPropertyValueTest extends TestCase
{
    protected ShipmentPropertyValue $spvService;

    protected ShipmentProperty $shipmentPropertyService;

    protected Shipment $shipmentService;

    protected int $personTypeId;

    protected int $orderId;

    protected int $deliveryId;

    protected int $shipmentId;

    protected int $propertyGroupId;

    protected int $propertyId1;

    protected int $propertyId2;

    protected string $propertyName1 = 'SPV Test Property 1';

    protected string $propertyName2 = 'SPV Test Property 2';

    #[\Override]
    protected function setUp(): void
    {
        $saleServiceBuilder = Factory::getServiceBuilder()->getSaleScope();
        $this->spvService = $saleServiceBuilder->shipmentPropertyValue();
        $this->shipmentPropertyService = $saleServiceBuilder->shipmentProperty();
        $this->shipmentService = $saleServiceBuilder->shipment();

        $this->personTypeId = $this->createPersonType();
        $this->orderId = $this->createOrder();
        $this->deliveryId = $this->getDeliveryId();
        $this->shipmentId = $this->createShipment();

    // Create property group for this person type
    $this->propertyGroupId = $this->createPropertyGroup('SPV Test Group');

        // Two shipment properties to set values for
    $this->propertyId1 = $this->createShipmentProperty($this->propertyName1);
    $this->propertyId2 = $this->createShipmentProperty($this->propertyName2);
    }

    #[\Override]
    protected function tearDown(): void
    {
        // Clean-up in reverse order
        if (isset($this->propertyId1)) {
            try { $this->shipmentPropertyService->delete($this->propertyId1); } catch (\Throwable) {}
        }

        if (isset($this->propertyId2)) {
            try { $this->shipmentPropertyService->delete($this->propertyId2); } catch (\Throwable) {}
        }

        if (isset($this->shipmentId)) {
            try { $this->shipmentService->delete($this->shipmentId); } catch (\Throwable) {}
        }

        if (isset($this->orderId)) {
            try { $this->deleteOrder($this->orderId); } catch (\Throwable) {}
        }

        if (isset($this->propertyGroupId)) {
            try { $this->deletePropertyGroup($this->propertyGroupId); } catch (\Throwable) {}
        }

        if (isset($this->personTypeId)) {
            try { $this->deletePersonType($this->personTypeId); } catch (\Throwable) {}
        }
    }

    protected function createPersonType(): int
    {
        $personTypeService = Factory::getServiceBuilder()->getSaleScope()->personType();
        $addedPersonTypeResult = $personTypeService->add([
            'name' => 'Test Person Type for SPV',
            'sort' => 100,
            'active' => 'Y'
        ]);
        return $addedPersonTypeResult->getId();
    }

    protected function createPropertyGroup(string $name): int
    {
        $propertyGroup = Factory::getServiceBuilder()->getSaleScope()->propertyGroup();
        $propertyGroupAddResult = $propertyGroup->add([
            'name' => $name,
            'personTypeId' => $this->personTypeId,
            'sort' => 100,
        ]);
        return $propertyGroupAddResult->propertyGroup()->id;
    }

    protected function deletePropertyGroup(int $id): void
    {
        $propertyGroup = Factory::getServiceBuilder()->getSaleScope()->propertyGroup();
        $propertyGroup->delete($id);
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

    protected function createShipmentProperty(string $name): int
    {
        $addedShipmentPropertyResult = $this->shipmentPropertyService->add([
            'name' => $name,
            'type' => 'STRING',
            'required' => 'N',
            'sort' => 100,
            'personTypeId' => $this->personTypeId,
            'propsGroupId' => $this->propertyGroupId,
        ]);
        return $addedShipmentPropertyResult->getId();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testModify(): void
    {
        $values = [
            ['shipmentPropsId' => $this->propertyId1, 'value' => 'Comments value'],
            ['shipmentPropsId' => $this->propertyId2, 'value' => 'Description value'],
        ];

        $updatedShipmentPropertyValueResult = $this->spvService->modify([
            'shipment' => [
                'id' => $this->shipmentId,
                'propertyValues' => $values,
            ],
        ]);

        self::assertTrue($updatedShipmentPropertyValueResult->isSuccess());

        // Verify via list for this shipment
        $list = $this->spvService->list(['id','name','value','shipmentId','shipmentPropsId'], ['shipmentId' => $this->shipmentId]);
        $items = $list->getPropertyValues();
        $byName = static function(array $its, string $name): ?object {
            foreach ($its as $it) { if ((string)$it->name === $name) { return $it; } }

            return null;
        };
        $pv1 = $byName($items, $this->propertyName1);
        $pv2 = $byName($items, $this->propertyName2);
        self::assertNotNull($pv1, 'Property value for property 1 not found');
        self::assertNotNull($pv2, 'Property value for property 2 not found');
        self::assertEquals('Comments value', (string)$pv1->value);
        self::assertEquals('Description value', (string)$pv2->value);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGet(): void
    {
        // Ensure values exist
        $this->spvService->modify([
            'shipment' => [
                'id' => $this->shipmentId,
                'propertyValues' => [
                    ['shipmentPropsId' => $this->propertyId1, 'value' => 'Get value 1'],
                ],
            ],
        ]);
    // find by name via list
    $items = $this->spvService->list(['id','name','value'], ['shipmentId' => $this->shipmentId])->getPropertyValues();
    $pv = null;
    foreach ($items as $item) { if ((string)$item->name === $this->propertyName1) { $pv = $item; break; } }

    self::assertNotNull($pv, 'Property value for property 1 not found in list');

    $shipmentPropertyValueItemResult = $this->spvService->get($pv->id)->propertyValue();
        self::assertEquals($pv->id, $shipmentPropertyValueItemResult->id);
        self::assertEquals('Get value 1', (string)$shipmentPropertyValueItemResult->value);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testList(): void
    {
        // Ensure two values exist
        $this->spvService->modify([
            'shipment' => [
                'id' => $this->shipmentId,
                'propertyValues' => [
                    ['shipmentPropsId' => $this->propertyId1, 'value' => 'List value 1'],
                    ['shipmentPropsId' => $this->propertyId2, 'value' => 'List value 2'],
                ],
            ],
        ]);

        $list = $this->spvService->list(
            ['id', 'name', 'value', 'shipmentId', 'shipmentPropsId'],
            ['shipmentId' => $this->shipmentId]
        );

        $items = $list->getPropertyValues();
        self::assertNotEmpty($items);
        foreach ($items as $item) {
            self::assertEquals($this->shipmentId, $item->shipmentId);
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelete(): void
    {
        // Create an extra property and value to delete to avoid interfering with other tests
        $deletePropId = $this->createShipmentProperty('SPV Delete Property');

        $this->spvService->modify([
            'shipment' => [
                'id' => $this->shipmentId,
                'propertyValues' => [
                    ['shipmentPropsId' => $deletePropId, 'value' => 'To delete'],
                ],
            ],
        ]);
    // find by name in list
    $items = $this->spvService->list(['id','name'], ['shipmentId' => $this->shipmentId])->getPropertyValues();
    $pv = null;
    foreach ($items as $item) { if ((string)$item->name === 'SPV Delete Property') { $pv = $item; break; } }

    self::assertNotNull($pv, 'Property value to delete not found');

    $deletedItemResult = $this->spvService->delete($pv->id);
        self::assertTrue($deletedItemResult->isSuccess());

        // cleanup the extra property
        $this->shipmentPropertyService->delete($deletePropId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetFields(): void
    {
    $fields = $this->spvService->getFields()->getFieldsDescription();
    self::assertIsArray($fields);
    self::assertNotEmpty($fields);
    }
}
