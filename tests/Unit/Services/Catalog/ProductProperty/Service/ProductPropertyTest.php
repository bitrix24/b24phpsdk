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

namespace Bitrix24\SDK\Tests\Unit\Services\Catalog\ProductProperty\Service;

use Bitrix24\SDK\Core\ApiLevelErrorHandler;
use Bitrix24\SDK\Core\Commands\Command;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Services\Catalog\ProductProperty\Result\DeletedProductPropertyResult;
use Bitrix24\SDK\Services\Catalog\ProductProperty\Result\ProductPropertiesResult;
use Bitrix24\SDK\Services\Catalog\ProductProperty\Result\ProductPropertyFieldsResult;
use Bitrix24\SDK\Services\Catalog\ProductProperty\Result\ProductPropertyResult;
use Bitrix24\SDK\Services\Catalog\ProductProperty\Service\Batch;
use Bitrix24\SDK\Services\Catalog\ProductProperty\Service\ProductProperty;
use Bitrix24\SDK\Tests\Unit\Stubs\NullBatch;
use Bitrix24\SDK\Tests\Unit\Stubs\NullCore;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\Response\MockResponse;

#[CoversClass(ProductProperty::class)]
class ProductPropertyTest extends TestCase
{
    private ProductProperty $service;

    #[\Override]
    protected function setUp(): void
    {
        $this->service = new ProductProperty(
            new Batch(new NullBatch(), new NullLogger()),
            new NullCore(),
            new NullLogger()
        );
    }

    #[Test]
    public function testAddReturnsProductPropertyResult(): void
    {
        $this->assertInstanceOf(
            ProductPropertyResult::class,
            $this->service->add(['iblockId' => 19, 'name' => 'Size', 'propertyType' => 'S'])
        );
    }

    #[Test]
    public function testUpdateReturnsProductPropertyResult(): void
    {
        $this->assertInstanceOf(
            ProductPropertyResult::class,
            $this->service->update(115, ['iblockId' => 19, 'name' => 'Size'])
        );
    }

    #[Test]
    public function testGetReturnsProductPropertyResult(): void
    {
        $this->assertInstanceOf(ProductPropertyResult::class, $this->service->get(115));
    }

    #[Test]
    public function testListReturnsProductPropertiesResult(): void
    {
        $this->assertInstanceOf(ProductPropertiesResult::class, $this->service->list());
    }

    #[Test]
    public function testDeleteReturnsDeletedProductPropertyResult(): void
    {
        $this->assertInstanceOf(DeletedProductPropertyResult::class, $this->service->delete(115));
    }

    #[Test]
    public function testGetFieldsReturnsProductPropertyFieldsResult(): void
    {
        $this->assertInstanceOf(ProductPropertyFieldsResult::class, $this->service->getFields());
    }

    #[Test]
    #[TestDox('add() sends fields nested under the fields key')]
    public function testAddSendsNestedFields(): void
    {
        [$method, $captured] = $this->call(
            static fn (ProductProperty $service) => $service->add(['iblockId' => 19, 'name' => 'Size'])
        );

        $this->assertSame('catalog.productProperty.add', $method);
        $this->assertSame(['iblockId' => 19, 'name' => 'Size'], $captured['fields']);
    }

    #[Test]
    #[TestDox('update() sends id and nested fields')]
    public function testUpdateSendsIdAndFields(): void
    {
        [$method, $captured] = $this->call(
            static fn (ProductProperty $service) => $service->update(115, ['iblockId' => 19, 'name' => 'Size'])
        );

        $this->assertSame('catalog.productProperty.update', $method);
        $this->assertSame(115, $captured['id']);
        $this->assertSame(['iblockId' => 19, 'name' => 'Size'], $captured['fields']);
    }

    #[Test]
    #[TestDox('get() sends the property id')]
    public function testGetSendsId(): void
    {
        [$method, $captured] = $this->call(static fn (ProductProperty $service) => $service->get(115));

        $this->assertSame('catalog.productProperty.get', $method);
        $this->assertSame(115, $captured['id']);
    }

    #[Test]
    #[TestDox('list() sends select, filter and order')]
    public function testListSendsSelectFilterOrder(): void
    {
        [$method, $captured] = $this->call(
            static fn (ProductProperty $service) => $service->list(['id', 'name'], ['iblockId' => 19], ['id' => 'ASC'])
        );

        $this->assertSame('catalog.productProperty.list', $method);
        $this->assertSame(['id', 'name'], $captured['select']);
        $this->assertSame(['iblockId' => 19], $captured['filter']);
        $this->assertSame(['id' => 'ASC'], $captured['order']);
    }

    #[Test]
    #[TestDox('delete() sends the property id')]
    public function testDeleteSendsId(): void
    {
        [$method, $captured] = $this->call(static fn (ProductProperty $service) => $service->delete(115));

        $this->assertSame('catalog.productProperty.delete', $method);
        $this->assertSame(115, $captured['id']);
    }

    /**
     * @return array{0: string, 1: array<string, mixed>}
     */
    private function call(callable $action): array
    {
        $method = null;
        $captured = [];
        $response = new Response(
            new MockResponse(''),
            new Command('', []),
            new ApiLevelErrorHandler(new NullLogger()),
            new NullLogger()
        );

        $core = $this->createStub(CoreInterface::class);
        $core->method('call')->willReturnCallback(
            function (string $apiMethod, array $parameters = []) use (&$method, &$captured, $response): Response {
                $method = $apiMethod;
                $captured = $parameters;

                return $response;
            }
        );

        $action(new ProductProperty(new Batch(new NullBatch(), new NullLogger()), $core, new NullLogger()));

        return [$method, $captured];
    }
}
