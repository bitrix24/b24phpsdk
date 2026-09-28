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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\DocumentContractor\Result;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Catalog\Document\Service\Document;
use Bitrix24\SDK\Services\Catalog\DocumentContractor\Result\DocumentContractorItemResult;
use Bitrix24\SDK\Services\Catalog\DocumentContractor\Service\DocumentContractor;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(DocumentContractorItemResult::class)]
class DocumentContractorItemResultTest extends TestCase
{
    use CustomBitrix24Assertions;

    private DocumentContractor $documentContractorService;

    private Document $documentService;

    private CoreInterface $core;

    private int $documentId;

    private int $contactId;

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function setUp(): void
    {
        $serviceBuilder = Factory::getServiceBuilder();
        $this->documentContractorService = $serviceBuilder->getCatalogScope()->documentContractor();
        $this->documentService = $serviceBuilder->getCatalogScope()->document();
        $this->core = Factory::getCore();

        $this->documentId = $this->documentService->add([
            'docType' => 'A',
            'currency' => 'USD',
            'responsibleId' => 1,
            'title' => sprintf('test document contractor annotations %s', time()),
        ])->document()->id;

        $this->contactId = (int) $this->core->call('crm.contact.add', [
            'fields' => ['NAME' => sprintf('test contractor contact annotations %s', time())],
        ])->getResponseData()->getResult();

        $this->documentContractorService->add([
            'documentId' => $this->documentId,
            'entityTypeId' => 3,
            'entityId' => $this->contactId,
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
            $this->core->call('crm.contact.delete', ['id' => $this->contactId]);
        } catch (\Throwable) {
            // already removed, ignore
        }
    }

    #[Test]
    #[TestDox('all fields in DocumentContractorItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllFieldsAreAnnotated(): void
    {
        $rawItems = $this->documentContractorService->list([], ['documentId' => $this->documentId])
            ->getCoreResponse()->getResponseData()->getResult()['documentContractor'];
        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($rawItems[0]), DocumentContractorItemResult::class);
    }

    #[Test]
    #[TestDox('all fields in DocumentContractorItemResult have valid type casting in magic getters')]
    public function testAllFieldsHasValidTypeCastingInMagicGetters(): void
    {
        $bindings = $this->documentContractorService->list([], ['documentId' => $this->documentId])->getDocumentContractors();
        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations($bindings[0], DocumentContractorItemResult::class);
    }
}
