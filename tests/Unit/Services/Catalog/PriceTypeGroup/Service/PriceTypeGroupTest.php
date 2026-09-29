<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Services\Catalog\PriceTypeGroup\Service;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Services\Catalog\PriceTypeGroup\Batch as PriceTypeGroupBatch;
use Bitrix24\SDK\Services\Catalog\PriceTypeGroup\Result\PriceTypeGroupFieldsResult;
use Bitrix24\SDK\Services\Catalog\PriceTypeGroup\Result\PriceTypeGroupResult;
use Bitrix24\SDK\Services\Catalog\PriceTypeGroup\Result\PriceTypeGroupsResult;
use Bitrix24\SDK\Services\Catalog\PriceTypeGroup\Service\Batch;
use Bitrix24\SDK\Services\Catalog\PriceTypeGroup\Service\PriceTypeGroup;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(PriceTypeGroup::class)]
class PriceTypeGroupTest extends TestCase
{
    public function testAddBuildsParameters(): void
    {
        $core = $this->mockCore('catalog.priceTypeGroup.add', [
            'fields' => ['catalogGroupId' => 9, 'groupId' => 23, 'access' => 'Y'],
        ]);

        self::assertInstanceOf(
            PriceTypeGroupResult::class,
            $this->makeService($core)->add(['catalogGroupId' => 9, 'groupId' => 23, 'access' => 'Y'])
        );
    }

    public function testListBuildsParameters(): void
    {
        $core = $this->mockCore('catalog.priceTypeGroup.list', [
            'select' => ['id', 'catalogGroupId'],
            'filter' => ['catalogGroupId' => 9],
            'order' => ['id' => 'ASC'],
            'start' => 0,
        ]);

        self::assertInstanceOf(
            PriceTypeGroupsResult::class,
            $this->makeService($core)->list(['id', 'catalogGroupId'], ['catalogGroupId' => 9], ['id' => 'ASC'], 0)
        );
    }

    public function testDeleteBuildsParameters(): void
    {
        $core = $this->mockCore('catalog.priceTypeGroup.delete', ['id' => 109]);

        self::assertInstanceOf(DeletedItemResult::class, $this->makeService($core)->delete(109));
    }

    public function testGetFieldsBuildsParameters(): void
    {
        $core = $this->mockCore('catalog.priceTypeGroup.getFields', []);

        self::assertInstanceOf(PriceTypeGroupFieldsResult::class, $this->makeService($core)->getFields());
    }

    private function makeService(CoreInterface $core): PriceTypeGroup
    {
        return new PriceTypeGroup(
            new Batch(new PriceTypeGroupBatch($core, new NullLogger()), new NullLogger()),
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
