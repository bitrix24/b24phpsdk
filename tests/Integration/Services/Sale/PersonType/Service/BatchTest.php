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

namespace Bitrix24\SDK\Tests\Integration\Services\Sale\PersonType\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Sale\PersonType\Service\Batch;
use Bitrix24\SDK\Services\Sale\PersonType\Service\PersonType;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Batch::class)]
#[CoversClass(\Bitrix24\SDK\Services\Sale\PersonType\Batch::class)]
class BatchTest extends TestCase
{
    private const ITEMS_COUNT = 3;

    private PersonType $personTypeService;

    #[\Override]
    protected function setUp(): void
    {
        $this->personTypeService = Fabric::getServiceBuilder()->getSaleScope()->personType();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('Batch::add, Batch::update, Batch::list, Batch::delete person types')]
    public function testAddUpdateListDelete(): void
    {
        $xmlId = 'BATCH_' . uniqid();
        $items = [];
        for ($i = 1; $i <= self::ITEMS_COUNT; $i++) {
            $items[] = [
                'name' => 'Batch person type ' . $i,
                'sort' => 100 + $i,
                'xmlId' => $xmlId,
            ];
        }

        $addedIds = [];
        foreach ($this->personTypeService->batch->add($items) as $addedItemResult) {
            $addedIds[] = $addedItemResult->getId();
        }

        $this->assertCount(self::ITEMS_COUNT, $addedIds);

        $updates = [];
        foreach ($addedIds as $id) {
            $updates[$id] = ['name' => 'Updated ' . $id];
        }

        foreach ($this->personTypeService->batch->update($updates) as $updatedItemResult) {
            $this->assertTrue($updatedItemResult->isSuccess());
        }

        $listed = [];
        foreach ($this->personTypeService->batch->list(['id', 'name'], ['xmlId' => $xmlId]) as $item) {
            $listed[(int)$item->id] = $item->name;
        }

        $this->assertCount(self::ITEMS_COUNT, $listed);
        foreach ($addedIds as $addedId) {
            $this->assertSame('Updated ' . $addedId, $listed[$addedId]);
        }

        $deletedCount = 0;
        foreach ($this->personTypeService->batch->delete($addedIds) as $deletedItemResult) {
            $this->assertTrue($deletedItemResult->isSuccess());
            $deletedCount++;
        }

        $this->assertSame(self::ITEMS_COUNT, $deletedCount);
    }
}
