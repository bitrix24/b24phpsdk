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

namespace Bitrix24\SDK\Tests\Integration\Services\Sale\PropertyGroup\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Sale\PropertyGroup\Service\Batch;
use Bitrix24\SDK\Services\Sale\PropertyGroup\Service\PropertyGroup;
use Bitrix24\SDK\Tests\Integration\Factory;
use Bitrix24\SDK\Tests\Integration\Services\Sale\SaleBatchFixturesTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Batch::class)]
#[CoversClass(\Bitrix24\SDK\Services\Sale\PropertyGroup\Batch::class)]
class BatchTest extends TestCase
{
    use SaleBatchFixturesTrait;

    private const ITEMS_COUNT = 3;

    private PropertyGroup $propertyGroupService;

    private int $personTypeId;

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->propertyGroupService = Factory::getServiceBuilder()->getSaleScope()->propertyGroup();
        $this->personTypeId = $this->createPersonType();
    }

    #[\Override]
    protected function tearDown(): void
    {
        $this->cleanup(fn () => Factory::getServiceBuilder()->getSaleScope()->personType()->delete($this->personTypeId));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('Batch::add, Batch::update, Batch::list, Batch::delete property groups')]
    public function testAddUpdateListDelete(): void
    {
        $items = [];
        for ($i = 1; $i <= self::ITEMS_COUNT; $i++) {
            $items[] = [
                'personTypeId' => $this->personTypeId,
                'name' => 'Batch property group ' . $i,
                'sort' => 100 + $i,
            ];
        }

        $addedIds = [];
        foreach ($this->propertyGroupService->batch->add($items) as $addedItemResult) {
            $addedIds[] = $addedItemResult->getId();
        }

        $this->assertCount(self::ITEMS_COUNT, $addedIds);

        $updates = [];
        foreach ($addedIds as $id) {
            $updates[$id] = ['personTypeId' => $this->personTypeId, 'name' => 'Updated ' . $id];
        }

        foreach ($this->propertyGroupService->batch->update($updates) as $updatedItemResult) {
            $this->assertTrue($updatedItemResult->isSuccess());
        }

        $listed = [];
        foreach ($this->propertyGroupService->batch->list(['id', 'name'], ['personTypeId' => $this->personTypeId]) as $item) {
            $listed[(int)$item->id] = $item->name;
        }

        foreach ($addedIds as $id) {
            $this->assertArrayHasKey($id, $listed);
            $this->assertSame('Updated ' . $id, $listed[$id]);
        }

        $deletedCount = 0;
        foreach ($this->propertyGroupService->batch->delete($addedIds) as $deletedItemResult) {
            $this->assertTrue($deletedItemResult->isSuccess());
            $deletedCount++;
        }

        $this->assertSame(self::ITEMS_COUNT, $deletedCount);
    }
}
