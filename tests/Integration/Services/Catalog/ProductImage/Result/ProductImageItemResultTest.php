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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\ProductImage\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Catalog\Catalog\Service\Catalog;
use Bitrix24\SDK\Services\Catalog\Product\Service\Product;
use Bitrix24\SDK\Services\Catalog\ProductImage\Result\ProductImageItemResult;
use Bitrix24\SDK\Services\Catalog\ProductImage\Service\ProductImage;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(ProductImageItemResult::class)]
class ProductImageItemResultTest extends TestCase
{
    use CustomBitrix24Assertions;

    // 1x1 transparent PNG pixel
    private const TEST_IMAGE_BASE64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=';

    private ProductImage $productImageService;

    private Product $productService;

    private int $productId;

    private int $productImageId;

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function setUp(): void
    {
        $serviceBuilder = Fabric::getServiceBuilder();
        $this->productImageService = $serviceBuilder->getCatalogScope()->productImage();
        $this->productService = $serviceBuilder->getCatalogScope()->product();
        $catalogService = $serviceBuilder->getCatalogScope()->catalog();

        $iblockId = $catalogService->list([], [], [], 1)->getCatalogs()[0]->iblockId;
        $this->productId = $this->productService->add([
            'iblockId' => $iblockId,
            'name' => sprintf('test product for image annotations %s', time()),
        ])->product()->id;

        $this->productImageId = $this->productImageService->add(
            $this->productId,
            ['test.jpeg', self::TEST_IMAGE_BASE64]
        )->productImage()->id;
    }

    #[\Override]
    protected function tearDown(): void
    {
        $this->productImageService->delete($this->productId, $this->productImageId);
        $this->productService->delete($this->productId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('all fields in ProductImageItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllFieldsAreAnnotated(): void
    {
        $rawItem = $this->productImageService->get($this->productId, $this->productImageId)
            ->getCoreResponse()->getResponseData()->getResult()['productImage'];

        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($rawItem), ProductImageItemResult::class);
    }
}
