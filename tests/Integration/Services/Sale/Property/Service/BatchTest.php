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

namespace Bitrix24\SDK\Tests\Integration\Services\Sale\Property\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Sale\Property\Service\Batch;
use Bitrix24\SDK\Services\Sale\Property\Service\Property;
use Bitrix24\SDK\Services\Sale\SaleServiceBuilder;
use Bitrix24\SDK\Tests\Integration\Fabric;
use Bitrix24\SDK\Tests\Integration\Services\Sale\SaleBatchFixturesTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Batch::class)]
#[CoversClass(\Bitrix24\SDK\Services\Sale\Property\Batch::class)]
class BatchTest extends TestCase
{
    use SaleBatchFixturesTrait;

    private const ITEMS_COUNT = 3;

    private SaleServiceBuilder $saleServiceBuilder;

    private Property $propertyService;

    private int $personTypeId;

    private int $propertyGroupId;

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->saleServiceBuilder = Fabric::getServiceBuilder()->getSaleScope();
        $this->propertyService = $this->saleServiceBuilder->property();
        $this->personTypeId = $this->createPersonType();
        $this->propertyGroupId = $this->createPropertyGroup($this->personTypeId);
    }

    #[\Override]
    protected function tearDown(): void
    {
        $this->cleanup(fn (): \Bitrix24\SDK\Core\Result\DeletedItemResult => $this->saleServiceBuilder->propertyGroup()->delete($this->propertyGroupId));
        $this->cleanup(fn (): \Bitrix24\SDK\Core\Result\DeletedItemResult => $this->saleServiceBuilder->personType()->delete($this->personTypeId));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('Batch::add, Batch::update, Batch::list, Batch::delete order properties')]
    public function testAddUpdateListDelete(): void
    {
        $items = [];
        for ($i = 1; $i <= self::ITEMS_COUNT; $i++) {
            $items[] = [
                'personTypeId' => $this->personTypeId,
                'propsGroupId' => $this->propertyGroupId,
                'name' => 'Batch property ' . $i,
                'type' => 'STRING',
                'required' => 'N',
                'multiple' => 'N',
                'sort' => 100 + $i,
            ];
        }

        $addedIds = [];
        foreach ($this->propertyService->batch->add($items) as $addedItemResult) {
            $addedIds[] = $addedItemResult->getId();
        }

        $this->assertCount(self::ITEMS_COUNT, $addedIds);

        $updates = [];
        foreach ($addedIds as $id) {
            $updates[$id] = ['name' => 'Updated ' . $id];
        }

        foreach ($this->propertyService->batch->update($updates) as $updatedItemResult) {
            $this->assertTrue($updatedItemResult->isSuccess());
        }

        $listed = [];
        foreach ($this->propertyService->batch->list(['id', 'name'], ['personTypeId' => $this->personTypeId]) as $item) {
            $listed[(int)$item->id] = $item->name;
        }

        $this->assertCount(self::ITEMS_COUNT, $listed);
        foreach ($addedIds as $addedId) {
            $this->assertSame('Updated ' . $addedId, $listed[$addedId]);
        }

        $deletedCount = 0;
        foreach ($this->propertyService->batch->delete($addedIds) as $deletedItemResult) {
            $this->assertTrue($deletedItemResult->isSuccess());
            $deletedCount++;
        }

        $this->assertSame(self::ITEMS_COUNT, $deletedCount);
    }
}
