<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Services\Catalog\PriceType\Service;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Services\Catalog\PriceType\Batch as PriceTypeBatch;
use Bitrix24\SDK\Services\Catalog\PriceType\Result\PriceTypeFieldsResult;
use Bitrix24\SDK\Services\Catalog\PriceType\Result\PriceTypeResult;
use Bitrix24\SDK\Services\Catalog\PriceType\Result\PriceTypesResult;
use Bitrix24\SDK\Services\Catalog\PriceType\Service\Batch;
use Bitrix24\SDK\Services\Catalog\PriceType\Service\PriceType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(PriceType::class)]
class PriceTypeTest extends TestCase
{
    public function testAddBuildsParameters(): void
    {
        $core = $this->mockCore('catalog.priceType.add', [
            'fields' => ['name' => 'Wholesale price', 'base' => 'N', 'sort' => 10, 'xmlId' => 'wholesale'],
        ]);

        self::assertInstanceOf(
            PriceTypeResult::class,
            $this->makeService($core)->add(['name' => 'Wholesale price', 'base' => 'N', 'sort' => 10, 'xmlId' => 'wholesale'])
        );
    }

    public function testUpdateBuildsParameters(): void
    {
        $core = $this->mockCore('catalog.priceType.update', [
            'id' => 2,
            'fields' => ['name' => 'Base wholesale price', 'base' => 'Y'],
        ]);

        self::assertInstanceOf(
            PriceTypeResult::class,
            $this->makeService($core)->update(2, ['name' => 'Base wholesale price', 'base' => 'Y'])
        );
    }

    public function testGetBuildsParameters(): void
    {
        $core = $this->mockCore('catalog.priceType.get', ['id' => 1]);

        self::assertInstanceOf(PriceTypeResult::class, $this->makeService($core)->get(1));
    }

    public function testListBuildsParameters(): void
    {
        $core = $this->mockCore('catalog.priceType.list', [
            'select' => ['id', 'name'],
            'filter' => ['modifiedBy' => 1],
            'order' => ['id' => 'ASC'],
        ]);

        self::assertInstanceOf(
            PriceTypesResult::class,
            $this->makeService($core)->list(['id', 'name'], ['modifiedBy' => 1], ['id' => 'ASC'])
        );
    }

    public function testDeleteBuildsParameters(): void
    {
        $core = $this->mockCore('catalog.priceType.delete', ['id' => 2]);

        self::assertInstanceOf(DeletedItemResult::class, $this->makeService($core)->delete(2));
    }

    public function testGetFieldsBuildsParameters(): void
    {
        $core = $this->mockCore('catalog.priceType.getFields', []);

        self::assertInstanceOf(PriceTypeFieldsResult::class, $this->makeService($core)->getFields());
    }

    private function makeService(CoreInterface $core): PriceType
    {
        return new PriceType(new Batch(new PriceTypeBatch($core, new NullLogger()), new NullLogger()), $core, new NullLogger());
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
