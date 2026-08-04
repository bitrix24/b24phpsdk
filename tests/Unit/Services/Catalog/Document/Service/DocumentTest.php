<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Services\Catalog\Document\Service;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Services\Catalog\Document\Batch as DocumentBatch;
use Bitrix24\SDK\Services\Catalog\Document\Result\DocumentFieldsResult;
use Bitrix24\SDK\Services\Catalog\Document\Result\DocumentModeStatusResult;
use Bitrix24\SDK\Services\Catalog\Document\Result\DocumentResult;
use Bitrix24\SDK\Services\Catalog\Document\Result\DocumentsResult;
use Bitrix24\SDK\Services\Catalog\Document\Service\Batch;
use Bitrix24\SDK\Services\Catalog\Document\Service\Document;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(Document::class)]
class DocumentTest extends TestCase
{
    public function testAddBuildsParameters(): void
    {
        $core = $this->mockCore('catalog.document.add', [
            'fields' => ['docType' => 'A', 'currency' => 'RUB', 'responsibleId' => 29],
        ]);

        self::assertInstanceOf(
            DocumentResult::class,
            $this->makeService($core)->add(['docType' => 'A', 'currency' => 'RUB', 'responsibleId' => 29])
        );
    }

    public function testUpdateBuildsParameters(): void
    {
        $core = $this->mockCore('catalog.document.update', [
            'id' => 142,
            'fields' => ['title' => 'Updated title'],
        ]);

        self::assertInstanceOf(
            DocumentResult::class,
            $this->makeService($core)->update(142, ['title' => 'Updated title'])
        );
    }

    public function testListBuildsParameters(): void
    {
        $core = $this->mockCore('catalog.document.list', [
            'select' => ['id', 'docType'],
            'filter' => ['docType' => 'A'],
        ]);

        self::assertInstanceOf(
            DocumentsResult::class,
            $this->makeService($core)->list(['id', 'docType'], ['docType' => 'A'])
        );
    }

    public function testDeleteBuildsParameters(): void
    {
        $core = $this->mockCore('catalog.document.delete', ['id' => 142]);

        self::assertInstanceOf(DeletedItemResult::class, $this->makeService($core)->delete(142));
    }

    public function testDeleteListBuildsParameters(): void
    {
        $core = $this->mockCore('catalog.document.deleteList', ['documentIds' => [142, 143]]);

        self::assertInstanceOf(DeletedItemResult::class, $this->makeService($core)->deleteList([142, 143]));
    }

    public function testConductBuildsParameters(): void
    {
        $core = $this->mockCore('catalog.document.conduct', ['id' => 142]);

        self::assertInstanceOf(DeletedItemResult::class, $this->makeService($core)->conduct(142));
    }

    public function testConductListBuildsParameters(): void
    {
        $core = $this->mockCore('catalog.document.conductList', ['documentIds' => [142, 143]]);

        self::assertInstanceOf(DeletedItemResult::class, $this->makeService($core)->conductList([142, 143]));
    }

    public function testCancelBuildsParameters(): void
    {
        $core = $this->mockCore('catalog.document.cancel', ['id' => 142]);

        self::assertInstanceOf(DeletedItemResult::class, $this->makeService($core)->cancel(142));
    }

    public function testCancelListBuildsParameters(): void
    {
        $core = $this->mockCore('catalog.document.cancelList', ['documentIds' => [142, 143]]);

        self::assertInstanceOf(DeletedItemResult::class, $this->makeService($core)->cancelList([142, 143]));
    }

    public function testGetFieldsBuildsParameters(): void
    {
        $core = $this->mockCore('catalog.document.getFields', []);

        self::assertInstanceOf(DocumentFieldsResult::class, $this->makeService($core)->getFields());
    }

    public function testModeStatusBuildsParameters(): void
    {
        $core = $this->mockCore('catalog.document.mode.status', []);

        self::assertInstanceOf(DocumentModeStatusResult::class, $this->makeService($core)->modeStatus());
    }

    private function makeService(CoreInterface $core): Document
    {
        return new Document(new Batch(new DocumentBatch($core, new NullLogger()), new NullLogger()), $core, new NullLogger());
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
