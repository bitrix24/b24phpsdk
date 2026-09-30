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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\Product\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Catalog\Catalog\Service\Catalog;
use Bitrix24\SDK\Services\Catalog\Product\Result\ProductItemResult;
use Bitrix24\SDK\Services\Catalog\Product\Service\Batch;
use Bitrix24\SDK\Services\Catalog\Product\Service\Product;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Batch::class)]
class BatchTest extends TestCase
{
    private Product $productService;

    private Catalog $catalogService;

    private int $iblockId;

    /** @var int[] */
    private array $productIds = [];

    #[\Override]
    protected function setUp(): void
    {
        $this->productService = Factory::getServiceBuilder()->getCatalogScope()->product();
        $this->catalogService = Factory::getServiceBuilder()->getCatalogScope()->catalog();
        $this->iblockId = $this->catalogService->list([], [], [], 0)->getCatalogs()[0]->iblockId;
    }

    #[\Override]
    protected function tearDown(): void
    {
        foreach ($this->productIds as $productId) {
            $this->productService->delete($productId);
        }

        $this->productIds = [];
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
                'name' => sprintf('sdk batch product 1 %s', time()),
            ],
            [
                'iblockId' => $this->iblockId,
                'name' => sprintf('sdk batch product 2 %s', time()),
            ],
        ];

        foreach ($this->productService->batch->add($items) as $result) {
            self::assertGreaterThan(0, $result->getId());
            $this->productIds[] = $result->getId();
        }

        self::assertCount(2, $this->productIds);

        $found = [];
        foreach (
            $this->productService->batch->list(
                ['id' => 'ASC'],
                ['iblockId' => $this->iblockId, '@id' => $this->productIds],
                ['id', 'iblockId', 'name']
            ) as $item
        ) {
            self::assertInstanceOf(ProductItemResult::class, $item);
            $found[] = $item->id;
        }

        foreach ($this->productIds as $productId) {
            self::assertContains($productId, $found);
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testBatchUpdate(): void
    {
        $productId = $this->productService->add([
            'iblockId' => $this->iblockId,
            'name' => sprintf('sdk batch product %s', time()),
        ])->product()->id;
        $this->productIds[] = $productId;

        $updatedName = sprintf('sdk batch product updated %s', time());
        $entityItems = [
            $productId => [
                'fields' => [
                    'name' => $updatedName,
                ],
            ],
        ];

        foreach ($this->productService->batch->update($entityItems) as $result) {
            self::assertEquals($updatedName, $result->product()->name);
        }

        self::assertEquals($updatedName, $this->productService->get($productId)->product()->name);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testBatchDelete(): void
    {
        $productIds = [];
        for ($i = 0; $i < 2; ++$i) {
            $productIds[] = $this->productService->add([
                'iblockId' => $this->iblockId,
                'name' => sprintf('sdk batch product to delete %s %s', $i, time()),
            ])->product()->id;
        }

        $deletedCount = 0;
        foreach ($this->productService->batch->delete($productIds) as $result) {
            self::assertTrue($result->isSuccess());
            ++$deletedCount;
        }

        self::assertSame(2, $deletedCount);
    }
}
