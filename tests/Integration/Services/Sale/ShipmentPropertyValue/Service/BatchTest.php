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

namespace Bitrix24\SDK\Tests\Integration\Services\Sale\ShipmentPropertyValue\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Sale\SaleServiceBuilder;
use Bitrix24\SDK\Services\Sale\ShipmentPropertyValue\Service\Batch;
use Bitrix24\SDK\Services\Sale\ShipmentPropertyValue\Service\ShipmentPropertyValue;
use Bitrix24\SDK\Tests\Integration\Factory;
use Bitrix24\SDK\Tests\Integration\Services\Sale\SaleBatchFixturesTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Batch::class)]
#[CoversClass(\Bitrix24\SDK\Services\Sale\ShipmentPropertyValue\Batch::class)]
class BatchTest extends TestCase
{
    use SaleBatchFixturesTrait;

    private const ITEMS_COUNT = 3;

    private SaleServiceBuilder $saleServiceBuilder;

    private ShipmentPropertyValue $shipmentPropertyValueService;

    private int $personTypeId;

    private int $orderId;

    private int $shipmentId;

    private int $propertyGroupId;

    /**
     * @var int[]
     */
    private array $shipmentPropertyIds = [];

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->saleServiceBuilder = Factory::getServiceBuilder()->getSaleScope();
        $this->shipmentPropertyValueService = $this->saleServiceBuilder->shipmentPropertyValue();
        $deliveryId = $this->getActiveDeliveryId();
        $this->personTypeId = $this->createPersonType();
        $this->propertyGroupId = $this->createPropertyGroup($this->personTypeId);
        for ($i = 1; $i <= self::ITEMS_COUNT; $i++) {
            $this->shipmentPropertyIds[] = $this->saleServiceBuilder->shipmentProperty()->add([
                'personTypeId' => $this->personTypeId,
                'propsGroupId' => $this->propertyGroupId,
                'name' => 'Batch shipment property ' . $i,
                'type' => 'STRING',
                'required' => 'N',
                'sort' => 100 + $i,
            ])->getId();
        }

        $this->orderId = $this->createOrder($this->personTypeId);
        $this->shipmentId = $this->createShipment($this->orderId, $deliveryId);
    }

    #[\Override]
    protected function tearDown(): void
    {
        $this->cleanup(fn () => $this->saleServiceBuilder->shipment()->delete($this->shipmentId));
        $this->cleanup(fn () => $this->saleServiceBuilder->order()->delete($this->orderId));
        foreach ($this->shipmentPropertyIds as $shipmentPropertyId) {
            $this->cleanup(fn () => $this->saleServiceBuilder->shipmentProperty()->delete($shipmentPropertyId));
        }

        $this->cleanup(fn () => $this->saleServiceBuilder->propertyGroup()->delete($this->propertyGroupId));
        $this->cleanup(fn () => $this->saleServiceBuilder->personType()->delete($this->personTypeId));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('Batch::list, Batch::delete shipment property values')]
    public function testListDelete(): void
    {
        $propertyValues = [];
        foreach ($this->shipmentPropertyIds as $shipmentPropertyId) {
            $propertyValues[] = ['shipmentPropsId' => $shipmentPropertyId, 'value' => 'Value ' . $shipmentPropertyId];
        }

        $this->assertTrue($this->shipmentPropertyValueService->modify([
            'shipment' => [
                'id' => $this->shipmentId,
                'propertyValues' => $propertyValues,
            ],
        ])->isSuccess());

        $valueIds = [];
        foreach ($this->shipmentPropertyValueService->batch->list(['id', 'shipmentPropsId', 'value'], ['shipmentId' => $this->shipmentId]) as $item) {
            if (in_array((int)$item->shipmentPropsId, $this->shipmentPropertyIds, true)) {
                $this->assertSame('Value ' . $item->shipmentPropsId, $item->value);
                $valueIds[] = (int)$item->id;
            }
        }

        $this->assertCount(self::ITEMS_COUNT, $valueIds);

        $deletedCount = 0;
        foreach ($this->shipmentPropertyValueService->batch->delete($valueIds) as $deletedItemResult) {
            $this->assertTrue($deletedItemResult->isSuccess());
            $deletedCount++;
        }

        $this->assertSame(self::ITEMS_COUNT, $deletedCount);
    }
}
