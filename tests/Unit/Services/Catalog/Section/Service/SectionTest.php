<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Services\Catalog\Section\Service;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Services\Catalog\Section\Batch as SectionBatch;
use Bitrix24\SDK\Services\Catalog\Section\Result\SectionFieldsResult;
use Bitrix24\SDK\Services\Catalog\Section\Result\SectionResult;
use Bitrix24\SDK\Services\Catalog\Section\Result\SectionsResult;
use Bitrix24\SDK\Services\Catalog\Section\Service\Batch;
use Bitrix24\SDK\Services\Catalog\Section\Service\Section;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(Section::class)]
class SectionTest extends TestCase
{
    public function testAddBuildsParameters(): void
    {
        $core = $this->mockCore('catalog.section.add', [
            'fields' => ['name' => 'Kids Toys', 'iblockId' => 14],
        ]);

        self::assertInstanceOf(
            SectionResult::class,
            $this->makeService($core)->add(['name' => 'Kids Toys', 'iblockId' => 14])
        );
    }

    public function testUpdateBuildsParameters(): void
    {
        $core = $this->mockCore('catalog.section.update', [
            'id' => 32,
            'fields' => ['name' => 'Updated name'],
        ]);

        self::assertInstanceOf(
            SectionResult::class,
            $this->makeService($core)->update(32, ['name' => 'Updated name'])
        );
    }

    public function testGetBuildsParameters(): void
    {
        $core = $this->mockCore('catalog.section.get', ['id' => 31]);

        self::assertInstanceOf(SectionResult::class, $this->makeService($core)->get(31));
    }

    public function testListBuildsParameters(): void
    {
        $core = $this->mockCore('catalog.section.list', [
            'select' => ['id', 'name'],
            'filter' => ['iblockId' => 14],
        ]);

        self::assertInstanceOf(
            SectionsResult::class,
            $this->makeService($core)->list(['id', 'name'], ['iblockId' => 14])
        );
    }

    public function testDeleteBuildsParameters(): void
    {
        $core = $this->mockCore('catalog.section.delete', ['id' => 31]);

        self::assertInstanceOf(DeletedItemResult::class, $this->makeService($core)->delete(31));
    }

    public function testGetFieldsBuildsParameters(): void
    {
        $core = $this->mockCore('catalog.section.getFields', []);

        self::assertInstanceOf(SectionFieldsResult::class, $this->makeService($core)->getFields());
    }

    private function makeService(CoreInterface $core): Section
    {
        return new Section(new Batch(new SectionBatch($core, new NullLogger()), new NullLogger()), $core, new NullLogger());
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
