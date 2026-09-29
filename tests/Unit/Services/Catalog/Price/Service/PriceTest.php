<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Services\Catalog\Price\Service;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Services\Catalog\Price\Batch as PriceBatch;
use Bitrix24\SDK\Services\Catalog\Price\Result\PriceFieldsResult;
use Bitrix24\SDK\Services\Catalog\Price\Result\PriceResult;
use Bitrix24\SDK\Services\Catalog\Price\Result\PricesResult;
use Bitrix24\SDK\Services\Catalog\Price\Service\Batch;
use Bitrix24\SDK\Services\Catalog\Price\Service\Price;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(Price::class)]
class PriceTest extends TestCase
{
    public function testAddBuildsParameters(): void
    {
        $core = $this->mockCore('catalog.price.add', [
            'fields' => ['productId' => 1, 'catalogGroupId' => 1, 'price' => 100.0, 'currency' => 'USD'],
        ]);

        self::assertInstanceOf(
            PriceResult::class,
            $this->makeService($core)->add(['productId' => 1, 'catalogGroupId' => 1, 'price' => 100.0, 'currency' => 'USD'])
        );
    }

    public function testUpdateBuildsParameters(): void
    {
        $core = $this->mockCore('catalog.price.update', [
            'id' => 1,
            'fields' => ['price' => 5000.0, 'currency' => 'RUB'],
        ]);

        self::assertInstanceOf(
            PriceResult::class,
            $this->makeService($core)->update(1, ['price' => 5000.0, 'currency' => 'RUB'])
        );
    }

    public function testModifyBuildsParameters(): void
    {
        $prices = [
            ['catalogGroupId' => 1, 'currency' => 'RUB', 'price' => 2001.0],
        ];

        $core = $this->mockCore('catalog.price.modify', [
            'fields' => ['product' => ['id' => 8, 'prices' => $prices]],
        ]);

        self::assertInstanceOf(PricesResult::class, $this->makeService($core)->modify(8, $prices));
    }

    public function testGetFieldsBuildsParameters(): void
    {
        $core = $this->mockCore('catalog.price.getFields', []);

        self::assertInstanceOf(PriceFieldsResult::class, $this->makeService($core)->getFields());
    }

    public function testListBuildsParameters(): void
    {
        $core = $this->mockCore('catalog.price.list', [
            'select' => ['id', 'price'],
            'filter' => ['productId' => 1],
            'order' => ['id' => 'ASC'],
        ]);

        self::assertInstanceOf(
            PricesResult::class,
            $this->makeService($core)->list(['id', 'price'], ['productId' => 1], ['id' => 'ASC'])
        );
    }

    public function testGetBuildsParameters(): void
    {
        $core = $this->mockCore('catalog.price.get', ['id' => 1]);

        self::assertInstanceOf(PriceResult::class, $this->makeService($core)->get(1));
    }

    public function testDeleteBuildsParameters(): void
    {
        $core = $this->mockCore('catalog.price.delete', ['id' => 1]);

        self::assertInstanceOf(DeletedItemResult::class, $this->makeService($core)->delete(1));
    }

    private function makeService(CoreInterface $core): Price
    {
        return new Price(new Batch(new PriceBatch($core, new NullLogger()), new NullLogger()), $core, new NullLogger());
    }

    private function mockCore(string $method, array $parameters): CoreInterface
    {
        $response = $this->createStub(Response::class);
        $core = $this->createMock(CoreInterface::class);
        $core->expects($this->once())
            ->method('call')
            ->with($method, $parameters)
            ->willReturn($response);

        return $core;
    }
}
