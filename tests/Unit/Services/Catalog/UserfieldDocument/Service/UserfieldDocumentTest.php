<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Services\Catalog\UserfieldDocument\Service;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Services\Catalog\UserfieldDocument\Batch as UserfieldDocumentBatch;
use Bitrix24\SDK\Services\Catalog\UserfieldDocument\Result\UserfieldDocumentResult;
use Bitrix24\SDK\Services\Catalog\UserfieldDocument\Result\UserfieldDocumentsResult;
use Bitrix24\SDK\Services\Catalog\UserfieldDocument\Service\Batch;
use Bitrix24\SDK\Services\Catalog\UserfieldDocument\Service\UserfieldDocument;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(UserfieldDocument::class)]
class UserfieldDocumentTest extends TestCase
{
    public function testListBuildsParameters(): void
    {
        $core = $this->mockCore('catalog.userfield.document.list', [
            'select' => ['documentType', 'documentId', 'field7097'],
            'filter' => ['documentType' => 'A', 'documentId' => 81],
            'order' => ['documentId' => 'ASC'],
            'start' => 0,
        ]);

        self::assertInstanceOf(
            UserfieldDocumentsResult::class,
            $this->makeService($core)->list(
                ['documentType', 'documentId', 'field7097'],
                ['documentType' => 'A', 'documentId' => 81],
                ['documentId' => 'ASC']
            )
        );
    }

    public function testUpdateBuildsParameters(): void
    {
        $core = $this->mockCore('catalog.userfield.document.update', [
            'documentId' => 81,
            'fields' => ['documentType' => 'A', 'field7097' => 'Test field value'],
        ]);

        self::assertInstanceOf(
            UserfieldDocumentResult::class,
            $this->makeService($core)->update(81, ['documentType' => 'A', 'field7097' => 'Test field value'])
        );
    }

    private function makeService(CoreInterface $core): UserfieldDocument
    {
        return new UserfieldDocument(
            new Batch(new UserfieldDocumentBatch($core, new NullLogger()), new NullLogger()),
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
