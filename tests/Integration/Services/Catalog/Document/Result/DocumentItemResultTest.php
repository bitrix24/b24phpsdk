<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <algonexys@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\Document\Result;

use Bitrix24\SDK\Services\Catalog\Document\Result\DocumentItemResult;
use Bitrix24\SDK\Services\Catalog\Document\Service\Document;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(DocumentItemResult::class)]
class DocumentItemResultTest extends TestCase
{
    use CustomBitrix24Assertions;

    private Document $documentService;

    private int $documentId;

    #[\Override]
    protected function setUp(): void
    {
        $this->documentService = Factory::getServiceBuilder(true)->getCatalogScope()->document();
        $this->documentId = $this->documentService->add([
            'docType' => 'A',
            'currency' => 'USD',
            'responsibleId' => 1,
            'title' => sprintf('test document annotations %s', time()),
        ])->document()->id;
    }

    #[\Override]
    protected function tearDown(): void
    {
        $this->documentService->delete($this->documentId);
    }

    #[Test]
    #[TestDox('all fields in DocumentItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllFieldsAreAnnotated(): void
    {
        $rawItem = $this->documentService->list([], ['id' => $this->documentId])
            ->getCoreResponse()->getResponseData()->getResult()['documents'][0];
        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($rawItem), DocumentItemResult::class);
    }

    #[Test]
    #[TestDox('all fields in DocumentItemResult have valid type casting in magic getters')]
    public function testAllFieldsHasValidTypeCastingInMagicGetters(): void
    {
        $documentItemResult = $this->documentService->list([], ['id' => $this->documentId])->getDocuments()[0];
        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations($documentItemResult, DocumentItemResult::class);
    }
}
