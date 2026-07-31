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
use Bitrix24\SDK\Services\Catalog\ProductImage\Result\ProductImageItemResult;
use Bitrix24\SDK\Services\Catalog\ProductImage\Service\ProductImage;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(ProductImage::class)]
class ProductImageTest extends TestCase
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
            'name' => sprintf('test product for images %s', time()),
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
    #[TestDox('test ProductImage::add')]
    public function testAdd(): void
    {
        $productImage = $this->productImageService->add(
            $this->productId,
            ['test.jpeg', self::TEST_IMAGE_BASE64],
            'MORE_PHOTO'
        )->productImage();

        $this->assertInstanceOf(ProductImageItemResult::class, $productImage);
        $this->assertGreaterThan(0, $productImage->id);
        $this->assertEquals($this->productId, $productImage->productId);

        // Cleanup
        $this->productImageService->delete($this->productId, $productImage->id);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('test ProductImage::get')]
    public function testGet(): void
    {
        $addedImage = $this->productImageService->add(
            $this->productId,
            ['test.jpeg', self::TEST_IMAGE_BASE64]
        )->productImage();

        $fetchedImage = $this->productImageService->get($this->productId, $addedImage->id)->productImage();
        $this->assertEquals($addedImage->id, $fetchedImage->id);
        $this->assertEquals($addedImage->name, $fetchedImage->name);

        // Cleanup
        $this->productImageService->delete($this->productId, $addedImage->id);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('test ProductImage::list')]
    public function testList(): void
    {
        $addedImage = $this->productImageService->add(
            $this->productId,
            ['test.jpeg', self::TEST_IMAGE_BASE64]
        )->productImage();

        $images = $this->productImageService->list($this->productId)->getProductImages();
        $this->assertCount(1, $images);
        $this->assertEquals($addedImage->id, $images[0]->id);

        // Cleanup
        $this->productImageService->delete($this->productId, $addedImage->id);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('test ProductImage::delete')]
    public function testDelete(): void
    {
        $addedImage = $this->productImageService->add(
            $this->productId,
            ['test.jpeg', self::TEST_IMAGE_BASE64]
        )->productImage();

        $this->assertTrue($this->productImageService->delete($this->productId, $addedImage->id)->isSuccess());

        $images = $this->productImageService->list($this->productId)->getProductImages();
        $this->assertCount(0, $images);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('test ProductImage::getFields')]
    public function testGetFields(): void
    {
        $fields = $this->productImageService->getFields()->getFieldsDescription();
        $this->assertIsArray($fields);
        $this->assertNotEmpty($fields);
        $this->assertArrayHasKey('id', $fields);
        $this->assertArrayHasKey('productId', $fields);
        $this->assertArrayHasKey('type', $fields);
    }
}
