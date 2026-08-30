<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Services\Catalog\DocumentElement\Service;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Services\Catalog\DocumentElement\Batch as DocumentElementBatch;
use Bitrix24\SDK\Services\Catalog\DocumentElement\Result\DocumentElementFieldsResult;
use Bitrix24\SDK\Services\Catalog\DocumentElement\Result\DocumentElementResult;
use Bitrix24\SDK\Services\Catalog\DocumentElement\Result\DocumentElementsResult;
use Bitrix24\SDK\Services\Catalog\DocumentElement\Service\Batch;
use Bitrix24\SDK\Services\Catalog\DocumentElement\Service\DocumentElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(DocumentElement::class)]
class DocumentElementTest extends TestCase
{
    public function testAddBuildsParameters(): void
    {
        $core = $this->mockCore('catalog.document.element.add', [
            'fields' => ['docId' => 64, 'elementId' => 312, 'storeTo' => 2, 'amount' => 15, 'purchasingPrice' => 1250.5],
        ]);

        self::assertInstanceOf(
            DocumentElementResult::class,
            $this->makeService($core)->add(['docId' => 64, 'elementId' => 312, 'storeTo' => 2, 'amount' => 15, 'purchasingPrice' => 1250.5])
        );
    }

    public function testUpdateBuildsParameters(): void
    {
        $core = $this->mockCore('catalog.document.element.update', [
            'id' => 148,
            'fields' => ['amount' => 12, 'purchasingPrice' => 1180, 'storeTo' => 2],
        ]);

        self::assertInstanceOf(
            DocumentElementResult::class,
            $this->makeService($core)->update(148, ['amount' => 12, 'purchasingPrice' => 1180, 'storeTo' => 2])
        );
    }

    public function testListBuildsParameters(): void
    {
        $core = $this->mockCore('catalog.document.element.list', [
            'select' => ['id', 'docId', 'elementId'],
            'filter' => ['docId' => 64],
            'order' => ['id' => 'ASC'],
        ]);

        self::assertInstanceOf(
            DocumentElementsResult::class,
            $this->makeService($core)->list(['id', 'docId', 'elementId'], ['docId' => 64], ['id' => 'ASC'])
        );
    }

    public function testDeleteBuildsParameters(): void
    {
        $core = $this->mockCore('catalog.document.element.delete', ['id' => 148]);

        self::assertInstanceOf(DeletedItemResult::class, $this->makeService($core)->delete(148));
    }

    public function testGetFieldsBuildsParameters(): void
    {
        $core = $this->mockCore('catalog.document.element.getFields', []);

        self::assertInstanceOf(DocumentElementFieldsResult::class, $this->makeService($core)->getFields());
    }

    private function makeService(CoreInterface $core): DocumentElement
    {
        return new DocumentElement(
            new Batch(new DocumentElementBatch($core, new NullLogger()), new NullLogger()),
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
