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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\ProductImage\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Catalog\Catalog\Service\Catalog;
use Bitrix24\SDK\Services\Catalog\Product\Service\Product;
use Bitrix24\SDK\Services\Catalog\ProductImage\Service\Batch;
use Bitrix24\SDK\Services\Catalog\ProductImage\Service\ProductImage;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Batch::class)]
class BatchTest extends TestCase
{
    // 1x1 transparent PNG pixel
    private const TEST_IMAGE_BASE64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=';

    protected ProductImage $productImageService;

    protected Product $productService;

    protected Catalog $catalogService;

    private int $productId;

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->productImageService = Factory::getServiceBuilder()->getCatalogScope()->productImage();
        $this->productService = Factory::getServiceBuilder()->getCatalogScope()->product();
        $this->catalogService = Factory::getServiceBuilder()->getCatalogScope()->catalog();

        $iblockId = $this->catalogService->list([], [], [], 1)->getCatalogs()[0]->iblockId;
        $this->productId = $this->productService->add([
            'iblockId' => $iblockId,
            'name' => sprintf('test product for batch images %s', time()),
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
    #[TestDox('Batch add product images')]
    public function testBatchAdd(): void
    {
        $items = [];
        for ($i = 1; $i <= 3; $i++) {
            $items[] = [
                'fields' => ['productId' => $this->productId, 'type' => 'MORE_PHOTO'],
                'fileContent' => [sprintf('test-%d.jpeg', $i), self::TEST_IMAGE_BASE64],
            ];
        }

        $ids = [];
        $cnt = 0;
        foreach ($this->productImageService->batch->add($items) as $added) {
            $cnt++;
            $ids[] = $added->id;
        }

        self::assertEquals(count($items), $cnt);

        // Cleanup
        $deleteItems = array_map(
            fn (int $id): array => ['productId' => $this->productId, 'id' => $id],
            $ids
        );
        foreach ($this->productImageService->batch->delete($deleteItems) as $deleted) {
            // consume generator to execute batch deletion
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('Batch delete product images')]
    public function testBatchDelete(): void
    {
        $ids = [];
        for ($i = 1; $i <= 3; $i++) {
            $ids[] = $this->productImageService->add(
                $this->productId,
                [sprintf('test-%d.jpeg', $i), self::TEST_IMAGE_BASE64]
            )->productImage()->id;
        }

        $deleteItems = array_map(
            fn (int $id): array => ['productId' => $this->productId, 'id' => $id],
            $ids
        );

        $delCnt = 0;
        foreach ($this->productImageService->batch->delete($deleteItems) as $deleted) {
            $delCnt++;
            self::assertTrue($deleted->isSuccess());
        }

        self::assertEquals(count($ids), $delCnt);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('Batch list product images for several products')]
    public function testBatchList(): void
    {
        $secondProductId = $this->productService->add([
            'iblockId' => $this->catalogService->list([], [], [], 1)->getCatalogs()[0]->iblockId,
            'name' => sprintf('test second product for batch images %s', time()),
        ])->product()->id;

        $firstImageId = $this->productImageService->add(
            $this->productId,
            ['first.jpeg', self::TEST_IMAGE_BASE64]
        )->productImage()->id;
        $secondImageId = $this->productImageService->add(
            $secondProductId,
            ['second.jpeg', self::TEST_IMAGE_BASE64]
        )->productImage()->id;

        $imagesByProduct = [];
        foreach ($this->productImageService->batch->list([$this->productId, $secondProductId]) as $key => $images) {
            $imagesByProduct[$key] = $images;
        }

        self::assertCount(2, $imagesByProduct);
        self::assertCount(1, $imagesByProduct[0]);
        self::assertCount(1, $imagesByProduct[1]);

        // Cleanup
        $this->productImageService->delete($this->productId, $firstImageId);
        $this->productImageService->delete($secondProductId, $secondImageId);

        $this->productService->delete($secondProductId);
    }
}
