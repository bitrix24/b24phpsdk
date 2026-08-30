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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\DocumentElement\Result;

use Bitrix24\SDK\Services\Catalog\Document\Service\Document;
use Bitrix24\SDK\Services\Catalog\DocumentElement\Result\DocumentElementItemResult;
use Bitrix24\SDK\Services\Catalog\DocumentElement\Service\DocumentElement;
use Bitrix24\SDK\Services\Catalog\Product\Service\Product;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(DocumentElementItemResult::class)]
class DocumentElementItemResultTest extends TestCase
{
    use CustomBitrix24Assertions;

    private DocumentElement $documentElementService;

    private Document $documentService;

    private Product $productService;

    private int $documentId;

    private int $productId;

    #[\Override]
    protected function setUp(): void
    {
        $serviceBuilder = Factory::getServiceBuilder();
        $this->documentElementService = $serviceBuilder->getCatalogScope()->documentElement();
        $this->documentService = $serviceBuilder->getCatalogScope()->document();
        $this->productService = $serviceBuilder->getCatalogScope()->product();

        $catalogService = $serviceBuilder->getCatalogScope()->catalog();
        $iblockId = $catalogService->list([], [], [], 1)->getCatalogs()[0]->iblockId;

        $this->productId = $this->productService->add([
            'iblockId' => $iblockId,
            'name' => sprintf('test document element annotations product %s', time()),
        ])->product()->id;

        $this->documentId = $this->documentService->add([
            'docType' => 'A',
            'currency' => 'USD',
            'responsibleId' => 1,
            'title' => sprintf('test document element annotations %s', time()),
        ])->document()->id;

        $this->documentElementService->add([
            'docId' => $this->documentId,
            'elementId' => $this->productId,
            'storeTo' => 0,
            'amount' => 2,
        ]);
    }

    #[\Override]
    protected function tearDown(): void
    {
        try {
            $this->documentService->delete($this->documentId);
        } catch (\Throwable) {
            // already removed, ignore
        }

        try {
            $this->productService->delete($this->productId);
        } catch (\Throwable) {
            // already removed, ignore
        }
    }

    #[Test]
    #[TestDox('all fields in DocumentElementItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllFieldsAreAnnotated(): void
    {
        $rawItem = $this->documentElementService->list([], ['docId' => $this->documentId])
            ->getCoreResponse()->getResponseData()->getResult()['documentElements'][0];
        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($rawItem), DocumentElementItemResult::class);
    }

    #[Test]
    #[TestDox('all fields in DocumentElementItemResult have valid type casting in magic getters')]
    public function testAllFieldsHasValidTypeCastingInMagicGetters(): void
    {
        $documentElementItemResult = $this->documentElementService->list([], ['docId' => $this->documentId])->getDocumentElements()[0];
        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations($documentElementItemResult, DocumentElementItemResult::class);
    }
}
