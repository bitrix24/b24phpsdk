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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\Section\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Catalog\Section\Service\Batch;
use Bitrix24\SDK\Services\Catalog\Section\Service\Section;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Batch::class)]
class BatchTest extends TestCase
{
    private Section $sectionService;

    private int $iblockId;

    #[\Override]
    protected function setUp(): void
    {
        $serviceBuilder = Fabric::getServiceBuilder(true);
        $this->sectionService = $serviceBuilder->getCatalogScope()->section();
        $this->iblockId = $serviceBuilder->getCatalogScope()->catalog()
            ->list([], [], [], 1)->getCatalogs()[0]->iblockId;
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('test Batch::add, Batch::update, Batch::delete')]
    public function testAddUpdateDelete(): void
    {
        $addedIds = [];
        foreach ($this->sectionService->batch->add([
            ['name' => sprintf('batch section %s', time()), 'iblockId' => $this->iblockId],
        ]) as $addedItemResult) {
            $addedIds[] = $addedItemResult->section()->id;
        }

        $this->assertCount(1, $addedIds);

        $updatePayload = [];
        foreach ($addedIds as $addedId) {
            $updatePayload[$addedId] = ['name' => 'updated batch section', 'iblockId' => $this->iblockId];
        }

        $updatedCount = 0;
        foreach ($this->sectionService->batch->update($updatePayload) as $updatedItemResult) {
            $this->assertSame('updated batch section', $updatedItemResult->section()->name);
            $updatedCount++;
        }

        $this->assertSame(1, $updatedCount);

        $deletedCount = 0;
        foreach ($this->sectionService->batch->delete($addedIds) as $deletedItemResult) {
            $this->assertTrue($deletedItemResult->isSuccess());
            $deletedCount++;
        }

        $this->assertSame(1, $deletedCount);
    }
}
