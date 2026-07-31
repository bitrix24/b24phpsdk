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

namespace Bitrix24\SDK\Tests\Unit\Services\Catalog\ProductImage\Service;

use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Services\Catalog\ProductImage\Batch as ProductImageBatch;
use Bitrix24\SDK\Services\Catalog\ProductImage\Result\ProductImageFieldsResult;
use Bitrix24\SDK\Services\Catalog\ProductImage\Result\ProductImageResult;
use Bitrix24\SDK\Services\Catalog\ProductImage\Result\ProductImagesResult;
use Bitrix24\SDK\Services\Catalog\ProductImage\Service\Batch;
use Bitrix24\SDK\Services\Catalog\ProductImage\Service\ProductImage;
use Bitrix24\SDK\Tests\Unit\Stubs\NullCore;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(ProductImage::class)]
class ProductImageTest extends TestCase
{
    private ProductImage $service;

    #[\Override]
    protected function setUp(): void
    {
        $nullCore = new NullCore();
        $nullLogger = new NullLogger();
        $this->service = new ProductImage(
            new Batch(new ProductImageBatch($nullCore, $nullLogger), $nullLogger),
            $nullCore,
            $nullLogger
        );
    }

    #[Test]
    public function testAddReturnsProductImageResult(): void
    {
        $this->assertInstanceOf(
            ProductImageResult::class,
            $this->service->add(1, ['test.jpeg', 'base64content'])
        );
    }

    #[Test]
    public function testGetReturnsProductImageResult(): void
    {
        $this->assertInstanceOf(
            ProductImageResult::class,
            $this->service->get(1, 1)
        );
    }

    #[Test]
    public function testListReturnsProductImagesResult(): void
    {
        $this->assertInstanceOf(
            ProductImagesResult::class,
            $this->service->list(1)
        );
    }

    #[Test]
    public function testDeleteReturnsDeletedItemResult(): void
    {
        $this->assertInstanceOf(
            DeletedItemResult::class,
            $this->service->delete(1, 1)
        );
    }

    #[Test]
    public function testGetFieldsReturnsProductImageFieldsResult(): void
    {
        $this->assertInstanceOf(
            ProductImageFieldsResult::class,
            $this->service->getFields()
        );
    }
}
