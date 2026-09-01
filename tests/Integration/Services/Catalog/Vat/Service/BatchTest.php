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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\Vat\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Catalog\Vat\Service\Batch;
use Bitrix24\SDK\Services\Catalog\Vat\Service\Vat;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Batch::class)]
class BatchTest extends TestCase
{
    private Vat $vatService;

    #[\Override]
    protected function setUp(): void
    {
        $this->vatService = Fabric::getServiceBuilder()->getCatalogScope()->vat();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('test Batch::add, Batch::update, Batch::delete')]
    public function testAddUpdateDelete(): void
    {
        $addedIds = [];
        foreach ($this->vatService->batch->add([
            ['name' => sprintf('batch vat %s', time()), 'rate' => 13, 'sort' => 60],
        ]) as $addedItemResult) {
            $addedIds[] = $addedItemResult->vat()->id;
        }

        $this->assertCount(1, $addedIds);

        $updatePayload = [];
        foreach ($addedIds as $addedId) {
            $updatePayload[$addedId] = ['name' => sprintf('batch vat updated %s', time()), 'rate' => 13, 'sort' => 70];
        }

        $updatedCount = 0;
        foreach ($this->vatService->batch->update($updatePayload) as $updatedItemResult) {
            $this->assertSame(70, $updatedItemResult->vat()->sort);
            $updatedCount++;
        }

        $this->assertSame(1, $updatedCount);

        $deletedCount = 0;
        foreach ($this->vatService->batch->delete($addedIds) as $deletedItemResult) {
            $this->assertTrue($deletedItemResult->isSuccess());
            $deletedCount++;
        }

        $this->assertSame(1, $deletedCount);
    }
}
