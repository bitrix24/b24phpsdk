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

namespace Bitrix24\SDK\Tests\Unit\Services\Catalog\Product\ProductService\Service;

use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Core\Result\FieldsResult;
use Bitrix24\SDK\Services\Catalog\Product\ProductService\Result\ProductServiceResult;
use Bitrix24\SDK\Services\Catalog\Product\ProductService\Result\ProductServicesResult;
use Bitrix24\SDK\Services\Catalog\Product\ProductService\Service\ProductService;
use Bitrix24\SDK\Tests\Unit\Stubs\NullCore;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(ProductService::class)]
class ProductServiceTest extends TestCase
{
    private ProductService $service;

    #[\Override]
    protected function setUp(): void
    {
        $this->service = new ProductService(new NullCore(), new NullLogger());
    }

    #[Test]
    public function testAddReturnsProductServiceResult(): void
    {
        $this->assertInstanceOf(
            ProductServiceResult::class,
            $this->service->add(['iblockId' => 1, 'name' => 'test'])
        );
    }

    #[Test]
    public function testUpdateReturnsProductServiceResult(): void
    {
        $this->assertInstanceOf(
            ProductServiceResult::class,
            $this->service->update(1, ['name' => 'test'])
        );
    }

    #[Test]
    public function testGetReturnsProductServiceResult(): void
    {
        $this->assertInstanceOf(
            ProductServiceResult::class,
            $this->service->get(1)
        );
    }

    #[Test]
    public function testListReturnsProductServicesResult(): void
    {
        $this->assertInstanceOf(
            ProductServicesResult::class,
            $this->service->list(['id', 'iblockId'], ['iblockId' => 1])
        );
    }

    #[Test]
    public function testDeleteReturnsDeletedItemResult(): void
    {
        $this->assertInstanceOf(
            DeletedItemResult::class,
            $this->service->delete(1)
        );
    }

    #[Test]
    public function testFieldsByFilterReturnsFieldsResult(): void
    {
        $this->assertInstanceOf(
            FieldsResult::class,
            $this->service->fieldsByFilter(1)
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
