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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\Price\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Catalog\Catalog\Service\Catalog;
use Bitrix24\SDK\Services\Catalog\Price\Service\Batch;
use Bitrix24\SDK\Services\Catalog\Price\Service\Price;
use Bitrix24\SDK\Services\Catalog\Product\Service\Product;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Batch::class)]
class BatchTest extends TestCase
{
    private Price $priceService;

    private Product $productService;

    private int $productId;

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function setUp(): void
    {
        $serviceBuilder = Fabric::getServiceBuilder();
        $this->priceService = $serviceBuilder->getCatalogScope()->price();
        $this->productService = $serviceBuilder->getCatalogScope()->product();

        $catalogService = $serviceBuilder->getCatalogScope()->catalog();
        $iblockId = $catalogService->list([], [], [], 1)->getCatalogs()[0]->iblockId;

        $this->productId = $this->productService->add([
            'iblockId' => $iblockId,
            'name' => sprintf('test product for batch price %s', time()),
        ])->product()->id;
    }

    #[\Override]
    protected function tearDown(): void
    {
        $this->productService->delete($this->productId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('test Batch::add, Batch::update, Batch::delete')]
    public function testAddUpdateDelete(): void
    {
        $addedIds = [];
        foreach ($this->priceService->batch->add([
            ['productId' => $this->productId, 'catalogGroupId' => 1, 'price' => 10.0, 'currency' => 'USD'],
        ]) as $addedItemResult) {
            $addedIds[] = $addedItemResult->price()->id;
        }

        $this->assertCount(1, $addedIds);

        $updatePayload = [];
        foreach ($addedIds as $id) {
            $updatePayload[$id] = ['price' => 20.0, 'currency' => 'USD'];
        }

        $updatedCount = 0;
        foreach ($this->priceService->batch->update($updatePayload) as $updatedItemResult) {
            $this->assertSame(20.0, $updatedItemResult->price()->price);
            $updatedCount++;
        }

        $this->assertSame(1, $updatedCount);

        $deletedCount = 0;
        foreach ($this->priceService->batch->delete($addedIds) as $deletedItemResult) {
            $this->assertTrue($deletedItemResult->isSuccess());
            $deletedCount++;
        }

        $this->assertSame(1, $deletedCount);
    }
}
