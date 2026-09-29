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

namespace Bitrix24\SDK\Tests\Integration\Services\Sale\PropertyRelation\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Sale\PropertyRelation\Service\Batch;
use Bitrix24\SDK\Services\Sale\PropertyRelation\Service\PropertyRelation;
use Bitrix24\SDK\Services\Sale\SaleServiceBuilder;
use Bitrix24\SDK\Tests\Integration\Factory;
use Bitrix24\SDK\Tests\Integration\Services\Sale\SaleBatchFixturesTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Batch::class)]
#[CoversClass(\Bitrix24\SDK\Services\Sale\PropertyRelation\Batch::class)]
class BatchTest extends TestCase
{
    use SaleBatchFixturesTrait;

    private const ITEMS_COUNT = 3;

    private SaleServiceBuilder $saleServiceBuilder;

    private PropertyRelation $propertyRelationService;

    private int $personTypeId;

    private int $propertyGroupId;

    private int $deliveryId;

    /**
     * @var int[]
     */
    private array $propertyIds = [];

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->saleServiceBuilder = Factory::getServiceBuilder()->getSaleScope();
        $this->propertyRelationService = $this->saleServiceBuilder->propertyRelation();
        $this->deliveryId = $this->getActiveDeliveryId();
        $this->personTypeId = $this->createPersonType();
        $this->propertyGroupId = $this->createPropertyGroup($this->personTypeId);
        for ($i = 0; $i < self::ITEMS_COUNT; $i++) {
            $this->propertyIds[] = $this->createProperty($this->personTypeId, $this->propertyGroupId);
        }
    }

    #[\Override]
    protected function tearDown(): void
    {
        foreach ($this->propertyIds as $propertyId) {
            $this->cleanup(fn () => $this->propertyRelationService->deleteByFilter([
                'entityId' => $this->deliveryId,
                'entityType' => 'D',
                'propertyId' => $propertyId,
            ]));
            $this->cleanup(fn () => $this->saleServiceBuilder->property()->delete($propertyId));
        }

        $this->cleanup(fn () => $this->saleServiceBuilder->propertyGroup()->delete($this->propertyGroupId));
        $this->cleanup(fn () => $this->saleServiceBuilder->personType()->delete($this->personTypeId));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('Batch::add, Batch::list property relations')]
    public function testAddList(): void
    {
        $items = [];
        foreach ($this->propertyIds as $propertyId) {
            $items[] = [
                'entityId' => $this->deliveryId,
                'entityType' => 'D',
                'propertyId' => $propertyId,
            ];
        }

        $addedCount = 0;
        foreach ($this->propertyRelationService->batch->add($items) as $addedItemResult) {
            $this->assertSame($this->deliveryId, $addedItemResult->getId());
            $addedCount++;
        }

        $this->assertSame(self::ITEMS_COUNT, $addedCount);

        $listedPropertyIds = [];
        foreach ($this->propertyRelationService->batch->list([], ['entityType' => 'D', 'entityId' => $this->deliveryId]) as $item) {
            $listedPropertyIds[] = (int)$item->propertyId;
        }

        foreach ($this->propertyIds as $propertyId) {
            $this->assertContains($propertyId, $listedPropertyIds);
        }
    }
}
