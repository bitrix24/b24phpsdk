<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Services\Catalog\PriceTypeLang\Service;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Services\Catalog\PriceTypeLang\Batch as PriceTypeLangBatch;
use Bitrix24\SDK\Services\Catalog\PriceTypeLang\Result\LanguagesResult;
use Bitrix24\SDK\Services\Catalog\PriceTypeLang\Result\PriceTypeLangFieldsResult;
use Bitrix24\SDK\Services\Catalog\PriceTypeLang\Result\PriceTypeLangResult;
use Bitrix24\SDK\Services\Catalog\PriceTypeLang\Result\PriceTypeLangsResult;
use Bitrix24\SDK\Services\Catalog\PriceTypeLang\Service\Batch;
use Bitrix24\SDK\Services\Catalog\PriceTypeLang\Service\PriceTypeLang;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(PriceTypeLang::class)]
class PriceTypeLangTest extends TestCase
{
    public function testAddBuildsParameters(): void
    {
        $core = $this->mockCore('catalog.priceTypeLang.add', [
            'fields' => ['catalogGroupId' => 1, 'lang' => 'kz', 'name' => 'PRICE'],
        ]);

        self::assertInstanceOf(
            PriceTypeLangResult::class,
            $this->makeService($core)->add(['catalogGroupId' => 1, 'lang' => 'kz', 'name' => 'PRICE'])
        );
    }

    public function testUpdateBuildsParameters(): void
    {
        $core = $this->mockCore('catalog.priceTypeLang.update', [
            'id' => 6,
            'fields' => ['name' => 'Base Price'],
        ]);

        self::assertInstanceOf(
            PriceTypeLangResult::class,
            $this->makeService($core)->update(6, ['name' => 'Base Price'])
        );
    }

    public function testGetBuildsParameters(): void
    {
        $core = $this->mockCore('catalog.priceTypeLang.get', ['id' => 2]);

        self::assertInstanceOf(PriceTypeLangResult::class, $this->makeService($core)->get(2));
    }

    public function testListBuildsParameters(): void
    {
        $core = $this->mockCore('catalog.priceTypeLang.list', [
            'select' => ['name', 'lang'],
            'filter' => ['catalogGroupId' => 1],
        ]);

        self::assertInstanceOf(
            PriceTypeLangsResult::class,
            $this->makeService($core)->list(['name', 'lang'], ['catalogGroupId' => 1])
        );
    }

    public function testDeleteBuildsParameters(): void
    {
        $core = $this->mockCore('catalog.priceTypeLang.delete', ['id' => 3]);

        self::assertInstanceOf(DeletedItemResult::class, $this->makeService($core)->delete(3));
    }

    public function testGetLanguagesBuildsParameters(): void
    {
        $core = $this->mockCore('catalog.priceTypeLang.getLanguages', []);

        self::assertInstanceOf(LanguagesResult::class, $this->makeService($core)->getLanguages());
    }

    public function testGetFieldsBuildsParameters(): void
    {
        $core = $this->mockCore('catalog.priceTypeLang.getFields', []);

        self::assertInstanceOf(PriceTypeLangFieldsResult::class, $this->makeService($core)->getFields());
    }

    private function makeService(CoreInterface $core): PriceTypeLang
    {
        return new PriceTypeLang(
            new Batch(new PriceTypeLangBatch($core, new NullLogger()), new NullLogger()),
            $core,
            new NullLogger()
        );
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
