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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\UserfieldDocument\Result;

use Bitrix24\SDK\Services\Catalog\Document\Service\Document;
use Bitrix24\SDK\Services\Catalog\UserfieldDocument\Result\UserfieldDocumentItemResult;
use Bitrix24\SDK\Services\Catalog\UserfieldDocument\Service\UserfieldDocument;
use Bitrix24\SDK\Tests\Builders\Services\Catalog\UserfieldDocument\CatalogDocumentUserfieldFixture;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * Requires application (OAuth) credentials — see tests/ApplicationBridge/.
 */
#[CoversClass(UserfieldDocumentItemResult::class)]
class UserfieldDocumentItemResultTest extends TestCase
{
    use CustomBitrix24Assertions;

    private UserfieldDocument $userfieldDocumentService;

    private Document $documentService;

    private string $userfieldCode;

    private int $documentId;

    #[\Override]
    protected function setUp(): void
    {
        $serviceBuilder = Factory::getServiceBuilder(true);
        $this->userfieldDocumentService = $serviceBuilder->getCatalogScope()->userfieldDocument();
        $this->documentService = $serviceBuilder->getCatalogScope()->document();
        $this->userfieldCode = CatalogDocumentUserfieldFixture::getOrCreateFieldCode(Factory::getCore(true));

        $this->documentId = $this->documentService->add([
            'docType' => 'A',
            'currency' => 'USD',
            'responsibleId' => 1,
            'title' => sprintf('test userfield document annotations %s', time()),
        ])->document()->id;

        $this->userfieldDocumentService->update($this->documentId, [
            'documentType' => 'A',
            $this->userfieldCode => sprintf('annotation test value %s', time()),
        ]);
    }

    #[\Override]
    protected function tearDown(): void
    {
        $this->documentService->delete($this->documentId);
    }

    #[Test]
    #[TestDox('all fixed system fields in UserfieldDocumentItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllFieldsAreAnnotated(): void
    {
        $rawItem = $this->userfieldDocumentService->list(
            ['documentType', 'documentId', $this->userfieldCode],
            ['documentType' => 'A', 'documentId' => $this->documentId]
        )->getCoreResponse()->getResponseData()->getResult()['documents'][0];

        // Dynamic userfield keys (fieldN) are portal-specific and intentionally not part of the
        // static PHPDoc contract — only the fixed system keys are checked here.
        $fixedSystemFields = array_intersect(array_keys($rawItem), ['documentId', 'documentType']);

        $this->assertBitrix24AllResultItemFieldsAnnotated($fixedSystemFields, UserfieldDocumentItemResult::class);
    }

    #[Test]
    #[TestDox('all fields in UserfieldDocumentItemResult have valid type casting in magic getters')]
    public function testAllFieldsHasValidTypeCastingInMagicGetters(): void
    {
        $itemResult = $this->userfieldDocumentService->list(
            ['documentType', 'documentId', $this->userfieldCode],
            ['documentType' => 'A', 'documentId' => $this->documentId]
        )->getDocuments()[0];

        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations($itemResult, UserfieldDocumentItemResult::class);
    }

    #[Test]
    #[TestDox('dynamic userfield value is accessible via magic getter even though it is unannotated')]
    public function testDynamicUserfieldValueIsAccessible(): void
    {
        $itemResult = $this->userfieldDocumentService->list(
            ['documentType', 'documentId', $this->userfieldCode],
            ['documentType' => 'A', 'documentId' => $this->documentId]
        )->getDocuments()[0];

        $this->assertNotEmpty($itemResult->{$this->userfieldCode});
    }
}
