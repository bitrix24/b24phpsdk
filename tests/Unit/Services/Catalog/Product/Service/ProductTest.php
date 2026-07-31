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

namespace Bitrix24\SDK\Tests\Unit\Services\Catalog\Product\Service;

use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Services\Catalog\Product\Result\ProductResult;
use Bitrix24\SDK\Services\Catalog\Product\Service\Batch;
use Bitrix24\SDK\Services\Catalog\Product\Service\Product;
use Bitrix24\SDK\Tests\Unit\Stubs\NullBatch;
use Bitrix24\SDK\Tests\Unit\Stubs\NullCore;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(Product::class)]
class ProductTest extends TestCase
{
    private Product $service;

    #[\Override]
    protected function setUp(): void
    {
        $this->service = new Product(new Batch(new NullBatch(), new NullLogger()), new NullCore(), new NullLogger());
    }

    #[Test]
    public function testUpdateReturnsProductResult(): void
    {
        $this->assertInstanceOf(
            ProductResult::class,
            $this->service->update(1, ['name' => 'test'])
        );
    }

    #[Test]
    public function testDownloadReturnsResponse(): void
    {
        $this->assertInstanceOf(
            Response::class,
            $this->service->download(1, 1, 'detailPicture')
        );
    }
}
