<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Services\Catalog\DocumentContractor\Service;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Services\Catalog\DocumentContractor\Batch as DocumentContractorBatch;
use Bitrix24\SDK\Services\Catalog\DocumentContractor\Result\DocumentContractorFieldsResult;
use Bitrix24\SDK\Services\Catalog\DocumentContractor\Result\DocumentContractorResult;
use Bitrix24\SDK\Services\Catalog\DocumentContractor\Result\DocumentContractorsResult;
use Bitrix24\SDK\Services\Catalog\DocumentContractor\Service\Batch;
use Bitrix24\SDK\Services\Catalog\DocumentContractor\Service\DocumentContractor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(DocumentContractor::class)]
class DocumentContractorTest extends TestCase
{
    public function testAddBuildsParameters(): void
    {
        $core = $this->mockCore('catalog.documentcontractor.add', [
            'fields' => ['documentId' => 42, 'entityTypeId' => 3, 'entityId' => 101],
        ]);

        self::assertInstanceOf(
            DocumentContractorResult::class,
            $this->makeService($core)->add(['documentId' => 42, 'entityTypeId' => 3, 'entityId' => 101])
        );
    }

    public function testListBuildsParameters(): void
    {
        $core = $this->mockCore('catalog.documentcontractor.list', [
            'select' => ['id', 'documentId'],
            'filter' => ['documentId' => 42],
            'order' => ['id' => 'ASC'],
            'start' => 0,
        ]);

        self::assertInstanceOf(
            DocumentContractorsResult::class,
            $this->makeService($core)->list(['id', 'documentId'], ['documentId' => 42], ['id' => 'ASC'], 0)
        );
    }

    public function testDeleteBuildsParameters(): void
    {
        $core = $this->mockCore('catalog.documentcontractor.delete', ['id' => 15]);

        self::assertInstanceOf(DeletedItemResult::class, $this->makeService($core)->delete(15));
    }

    public function testGetFieldsBuildsParameters(): void
    {
        $core = $this->mockCore('catalog.documentcontractor.getFields', []);

        self::assertInstanceOf(DocumentContractorFieldsResult::class, $this->makeService($core)->getFields());
    }

    private function makeService(CoreInterface $core): DocumentContractor
    {
        return new DocumentContractor(
            new Batch(new DocumentContractorBatch($core, new NullLogger()), new NullLogger()),
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
