<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Services\Catalog\Vat\Service;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Services\Catalog\Vat\Batch as VatBatch;
use Bitrix24\SDK\Services\Catalog\Vat\Result\VatFieldsResult;
use Bitrix24\SDK\Services\Catalog\Vat\Result\VatResult;
use Bitrix24\SDK\Services\Catalog\Vat\Result\VatsResult;
use Bitrix24\SDK\Services\Catalog\Vat\Service\Batch;
use Bitrix24\SDK\Services\Catalog\Vat\Service\Vat;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(Vat::class)]
class VatTest extends TestCase
{
    public function testAddBuildsParameters(): void
    {
        $core = $this->mockCore('catalog.vat.add', [
            'fields' => ['name' => 'Tax 13%', 'rate' => 13, 'sort' => 10, 'active' => 'Y'],
        ]);

        self::assertInstanceOf(
            VatResult::class,
            $this->makeService($core)->add(['name' => 'Tax 13%', 'rate' => 13, 'sort' => 10, 'active' => 'Y'])
        );
    }

    public function testUpdateBuildsParameters(): void
    {
        $core = $this->mockCore('catalog.vat.update', [
            'id' => 6,
            'fields' => ['name' => 'Tax 23%', 'rate' => 23],
        ]);

        self::assertInstanceOf(
            VatResult::class,
            $this->makeService($core)->update(6, ['name' => 'Tax 23%', 'rate' => 23])
        );
    }

    public function testGetBuildsParameters(): void
    {
        $core = $this->mockCore('catalog.vat.get', ['id' => 7]);

        self::assertInstanceOf(VatResult::class, $this->makeService($core)->get(7));
    }

    public function testListBuildsParameters(): void
    {
        $core = $this->mockCore('catalog.vat.list', [
            'select' => ['id', 'name', 'rate'],
            'filter' => ['>=sort' => 200],
            'order' => ['id' => 'ASC'],
        ]);

        self::assertInstanceOf(
            VatsResult::class,
            $this->makeService($core)->list(['id', 'name', 'rate'], ['>=sort' => 200], ['id' => 'ASC'])
        );
    }

    public function testDeleteBuildsParameters(): void
    {
        $core = $this->mockCore('catalog.vat.delete', ['id' => 7]);

        self::assertInstanceOf(DeletedItemResult::class, $this->makeService($core)->delete(7));
    }

    public function testGetFieldsBuildsParameters(): void
    {
        $core = $this->mockCore('catalog.vat.getFields', []);

        self::assertInstanceOf(VatFieldsResult::class, $this->makeService($core)->getFields());
    }

    private function makeService(CoreInterface $core): Vat
    {
        return new Vat(new Batch(new VatBatch($core, new NullLogger()), new NullLogger()), $core, new NullLogger());
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
