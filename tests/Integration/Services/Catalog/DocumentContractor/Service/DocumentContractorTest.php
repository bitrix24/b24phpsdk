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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\DocumentContractor\Service;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Catalog\Document\Service\Document;
use Bitrix24\SDK\Services\Catalog\DocumentContractor\Service\DocumentContractor;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(DocumentContractor::class)]
class DocumentContractorTest extends TestCase
{
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
        $serviceBuilder = Fabric::getServiceBuilder();
        $this->documentContractorService = $serviceBuilder->getCatalogScope()->documentContractor();
        $this->documentService = $serviceBuilder->getCatalogScope()->document();
        $this->core = Fabric::getCore();

        $this->documentId = $this->documentService->add([
            'docType' => 'A',
            'currency' => 'USD',
            'responsibleId' => 1,
            'title' => sprintf('test document contractor %s', time()),
        ])->document()->id;

        $this->contactId = (int) $this->core->call('crm.contact.add', [
            'fields' => ['NAME' => sprintf('test contractor contact %s', time())],
        ])->getResponseData()->getResult();
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

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('test DocumentContractor::add, DocumentContractor::list, DocumentContractor::delete')]
    public function testAddListDelete(): void
    {
        $addResult = $this->documentContractorService->add([
            'documentId' => $this->documentId,
            'entityTypeId' => 3,
            'entityId' => $this->contactId,
        ]);
        $this->assertSame($this->documentId, $addResult->documentContractor()->documentId);
        $this->assertSame(3, $addResult->documentContractor()->entityTypeId);
        $this->assertSame($this->contactId, $addResult->documentContractor()->entityId);
        $bindingId = $addResult->documentContractor()->id;

        $listResult = $this->documentContractorService->list([], ['documentId' => $this->documentId]);
        $bindings = $listResult->getDocumentContractors();
        $this->assertCount(1, $bindings);
        $this->assertSame($bindingId, $bindings[0]->id);

        $this->assertTrue($this->documentContractorService->delete($bindingId)->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('test DocumentContractor::getFields')]
    public function testGetFields(): void
    {
        $this->assertIsArray($this->documentContractorService->getFields()->getFieldsDescription());
    }
}
