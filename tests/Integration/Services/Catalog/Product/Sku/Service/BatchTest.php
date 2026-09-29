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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\Product\Sku\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Catalog\Catalog\Service\Catalog;
use Bitrix24\SDK\Services\Catalog\Product\Sku\Result\SkuItemResult;
use Bitrix24\SDK\Services\Catalog\Product\Sku\Service\Batch;
use Bitrix24\SDK\Services\Catalog\Product\Sku\Service\Sku;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Batch::class)]
class BatchTest extends TestCase
{
    private Sku $skuService;

    private Catalog $catalogService;

    private int $iblockId;

    /** @var int[] */
    private array $skuIds = [];

    #[\Override]
    protected function setUp(): void
    {
        $this->skuService = Fabric::getServiceBuilder()->getCatalogScope()->productSku();
        $this->catalogService = Fabric::getServiceBuilder()->getCatalogScope()->catalog();
        $this->iblockId = $this->getProductCatalogIblockId();
    }

    #[\Override]
    protected function tearDown(): void
    {
        foreach ($this->skuIds as $skuId) {
            $this->skuService->delete($skuId);
        }

        $this->skuIds = [];
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testBatchAddAndList(): void
    {
        $items = [
            [
                'iblockId' => $this->iblockId,
                'name' => sprintf('sdk batch sku 1 %s', time()),
            ],
            [
                'iblockId' => $this->iblockId,
                'name' => sprintf('sdk batch sku 2 %s', time()),
            ],
        ];

        foreach ($this->skuService->batch->add($items) as $result) {
            self::assertGreaterThan(0, $result->getId());
            $this->skuIds[] = $result->getId();
        }

        self::assertCount(2, $this->skuIds);

        $found = [];
        foreach (
            $this->skuService->batch->list(
                ['id' => 'ASC'],
                ['iblockId' => $this->iblockId, '@id' => $this->skuIds],
                ['id', 'iblockId', 'name']
            ) as $item
        ) {
            self::assertInstanceOf(SkuItemResult::class, $item);
            $found[] = $item->id;
        }

        foreach ($this->skuIds as $skuId) {
            self::assertContains($skuId, $found);
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testBatchUpdate(): void
    {
        $skuId = $this->skuService->add([
            'iblockId' => $this->iblockId,
            'name' => sprintf('sdk batch sku %s', time()),
        ])->sku()->id;
        $this->skuIds[] = $skuId;

        $updatedName = sprintf('sdk batch sku updated %s', time());
        $entityItems = [
            $skuId => [
                'fields' => [
                    'name' => $updatedName,
                ],
            ],
        ];

        foreach ($this->skuService->batch->update($entityItems) as $result) {
            self::assertEquals($updatedName, $result->sku()->name);
        }

        self::assertEquals($updatedName, $this->skuService->get($skuId)->sku()->name);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testBatchDelete(): void
    {
        $skuIds = [];
        for ($i = 0; $i < 2; ++$i) {
            $skuIds[] = $this->skuService->add([
                'iblockId' => $this->iblockId,
                'name' => sprintf('sdk batch sku to delete %s %s', $i, time()),
            ])->sku()->id;
        }

        $deletedCount = 0;
        foreach ($this->skuService->batch->delete($skuIds) as $result) {
            self::assertTrue($result->isSuccess());
            ++$deletedCount;
        }

        self::assertSame(2, $deletedCount);
    }

    /**
     * Parent (SKU) products live in the catalog that has no parent product catalog.
     *
     * @throws BaseException
     * @throws TransportException
     */
    private function getProductCatalogIblockId(): int
    {
        foreach ($this->catalogService->list([], [], [], 0)->getCatalogs() as $catalogItemResult) {
            if ($catalogItemResult->productIblockId === null) {
                return $catalogItemResult->iblockId;
            }
        }

        self::fail('products catalog not found');
    }
}
