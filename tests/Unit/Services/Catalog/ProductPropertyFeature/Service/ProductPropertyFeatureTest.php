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

namespace Bitrix24\SDK\Tests\Unit\Services\Catalog\ProductPropertyFeature\Service;

use Bitrix24\SDK\Core\ApiLevelErrorHandler;
use Bitrix24\SDK\Core\Commands\Command;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Services\Catalog\ProductPropertyFeature\Result\AvailableFeaturesResult;
use Bitrix24\SDK\Services\Catalog\ProductPropertyFeature\Result\ProductPropertyFeatureAddedResult;
use Bitrix24\SDK\Services\Catalog\ProductPropertyFeature\Result\ProductPropertyFeatureFieldsResult;
use Bitrix24\SDK\Services\Catalog\ProductPropertyFeature\Result\ProductPropertyFeatureResult;
use Bitrix24\SDK\Services\Catalog\ProductPropertyFeature\Result\ProductPropertyFeatureUpdatedResult;
use Bitrix24\SDK\Services\Catalog\ProductPropertyFeature\Result\ProductPropertyFeaturesResult;
use Bitrix24\SDK\Services\Catalog\ProductPropertyFeature\Service\Batch;
use Bitrix24\SDK\Services\Catalog\ProductPropertyFeature\Service\ProductPropertyFeature;
use Bitrix24\SDK\Tests\Unit\Stubs\NullBatch;
use Bitrix24\SDK\Tests\Unit\Stubs\NullCore;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\Response\MockResponse;

#[CoversClass(ProductPropertyFeature::class)]
class ProductPropertyFeatureTest extends TestCase
{
    private ProductPropertyFeature $service;

    #[\Override]
    protected function setUp(): void
    {
        $this->service = new ProductPropertyFeature(
            new Batch(new NullBatch(), new NullLogger()),
            new NullCore(),
            new NullLogger()
        );
    }

    #[Test]
    public function testAddReturnsProductPropertyFeatureAddedResult(): void
    {
        $this->assertInstanceOf(
            ProductPropertyFeatureAddedResult::class,
            $this->service->add([
                'propertyId' => 901,
                'moduleId' => 'iblock',
                'featureId' => 'LIST_PAGE_SHOW',
                'isEnabled' => 'Y',
            ])
        );
    }

    #[Test]
    public function testUpdateReturnsProductPropertyFeatureUpdatedResult(): void
    {
        $this->assertInstanceOf(
            ProductPropertyFeatureUpdatedResult::class,
            $this->service->update(101, [
                'propertyId' => 901,
                'moduleId' => 'iblock',
                'featureId' => 'LIST_PAGE_SHOW',
                'isEnabled' => 'N',
            ])
        );
    }

    #[Test]
    public function testGetReturnsProductPropertyFeatureResult(): void
    {
        $this->assertInstanceOf(ProductPropertyFeatureResult::class, $this->service->get(101));
    }

    #[Test]
    public function testListReturnsProductPropertyFeaturesResult(): void
    {
        $this->assertInstanceOf(ProductPropertyFeaturesResult::class, $this->service->list());
    }

    #[Test]
    public function testGetAvailableFeaturesByPropertyReturnsAvailableFeaturesResult(): void
    {
        $this->assertInstanceOf(AvailableFeaturesResult::class, $this->service->getAvailableFeaturesByProperty(901));
    }

    #[Test]
    public function testGetFieldsReturnsProductPropertyFeatureFieldsResult(): void
    {
        $this->assertInstanceOf(ProductPropertyFeatureFieldsResult::class, $this->service->getFields());
    }

    #[Test]
    public function testUpdateThrowsExceptionForNonPositiveId(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->service->update(0, ['propertyId' => 901, 'moduleId' => 'iblock', 'featureId' => 'X', 'isEnabled' => 'Y']);
    }

    #[Test]
    public function testGetThrowsExceptionForNonPositiveId(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->service->get(0);
    }

    #[Test]
    public function testGetAvailableFeaturesByPropertyThrowsExceptionForNonPositivePropertyId(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->service->getAvailableFeaturesByProperty(0);
    }

    #[Test]
    #[TestDox('add() calls catalog.productPropertyFeature.add with a nested fields object')]
    public function testAddSendsNestedFields(): void
    {
        [$method, $captured] = $this->call(static fn (ProductPropertyFeature $productPropertyFeature): \Bitrix24\SDK\Services\Catalog\ProductPropertyFeature\Result\ProductPropertyFeatureAddedResult => $productPropertyFeature->add([
            'propertyId' => 901,
            'moduleId' => 'iblock',
            'featureId' => 'LIST_PAGE_SHOW',
            'isEnabled' => 'Y',
        ]));

        $this->assertSame('catalog.productPropertyFeature.add', $method);
        $this->assertSame([
            'fields' => [
                'propertyId' => 901,
                'moduleId' => 'iblock',
                'featureId' => 'LIST_PAGE_SHOW',
                'isEnabled' => 'Y',
            ],
        ], $captured);
    }

    #[Test]
    #[TestDox('update() sends id and nested fields')]
    public function testUpdateSendsIdAndFields(): void
    {
        [$method, $captured] = $this->call(static fn (ProductPropertyFeature $productPropertyFeature): \Bitrix24\SDK\Services\Catalog\ProductPropertyFeature\Result\ProductPropertyFeatureUpdatedResult => $productPropertyFeature->update(101, [
            'propertyId' => 901,
            'moduleId' => 'iblock',
            'featureId' => 'LIST_PAGE_SHOW',
            'isEnabled' => 'N',
        ]));

        $this->assertSame('catalog.productPropertyFeature.update', $method);
        $this->assertSame(101, $captured['id']);
        $this->assertSame([
            'propertyId' => 901,
            'moduleId' => 'iblock',
            'featureId' => 'LIST_PAGE_SHOW',
            'isEnabled' => 'N',
        ], $captured['fields']);
    }

    #[Test]
    #[TestDox('get() sends the id')]
    public function testGetSendsId(): void
    {
        [$method, $captured] = $this->call(static fn (ProductPropertyFeature $productPropertyFeature): \Bitrix24\SDK\Services\Catalog\ProductPropertyFeature\Result\ProductPropertyFeatureResult => $productPropertyFeature->get(101));

        $this->assertSame('catalog.productPropertyFeature.get', $method);
        $this->assertSame(101, $captured['id']);
    }

    #[Test]
    #[TestDox('list() sends select, filter and order')]
    public function testListSendsSelectFilterOrder(): void
    {
        [$method, $captured] = $this->call(static fn (ProductPropertyFeature $productPropertyFeature): \Bitrix24\SDK\Services\Catalog\ProductPropertyFeature\Result\ProductPropertyFeaturesResult => $productPropertyFeature->list(
            ['id', 'propertyId'],
            ['propertyId' => 901],
            ['id' => 'ASC']
        ));

        $this->assertSame('catalog.productPropertyFeature.list', $method);
        $this->assertSame(['id', 'propertyId'], $captured['select']);
        $this->assertSame(['propertyId' => 901], $captured['filter']);
        $this->assertSame(['id' => 'ASC'], $captured['order']);
    }

    #[Test]
    #[TestDox('getAvailableFeaturesByProperty() sends propertyId')]
    public function testGetAvailableFeaturesByPropertySendsPropertyId(): void
    {
        [$method, $captured] = $this->call(
            static fn (ProductPropertyFeature $productPropertyFeature): \Bitrix24\SDK\Services\Catalog\ProductPropertyFeature\Result\AvailableFeaturesResult => $productPropertyFeature->getAvailableFeaturesByProperty(901)
        );

        $this->assertSame('catalog.productPropertyFeature.getAvailableFeaturesByProperty', $method);
        $this->assertSame(901, $captured['propertyId']);
    }

    #[Test]
    #[TestDox('getFields() calls catalog.productPropertyFeature.getFields with no parameters')]
    public function testGetFieldsSendsNoParameters(): void
    {
        [$method, $captured] = $this->call(static fn (ProductPropertyFeature $productPropertyFeature): \Bitrix24\SDK\Services\Catalog\ProductPropertyFeature\Result\ProductPropertyFeatureFieldsResult => $productPropertyFeature->getFields());

        $this->assertSame('catalog.productPropertyFeature.getFields', $method);
        $this->assertSame([], $captured);
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

        $action(new ProductPropertyFeature(
            new Batch(new NullBatch(), new NullLogger()),
            $core,
            new NullLogger()
        ));

        return [$method, $captured];
    }
}
