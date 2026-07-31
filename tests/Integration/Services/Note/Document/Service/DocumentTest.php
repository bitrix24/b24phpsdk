<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\Note\Document\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Note\Collection\Service\Collection;
use Bitrix24\SDK\Services\Note\Document\Service\Document;
use Bitrix24\SDK\Services\Note\Document\Service\DocumentSelectBuilder;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Document::class)]
class DocumentTest extends TestCase
{
    private Collection $collectionService;

    private Document $documentService;

    private int $collectionId;

    /**
     * @var int[]
     */
    private array $createdDocumentIds = [];

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function setUp(): void
    {
        $serviceBuilder = Factory::getServiceBuilder();
        $this->collectionService = $serviceBuilder->getNoteScope()->collection();
        $this->documentService = $serviceBuilder->getNoteScope()->document();

        $this->collectionId = $this->collectionService->add('SDK document test collection ' . time())
            ->collection()->id;
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function tearDown(): void
    {
        foreach ($this->createdDocumentIds as $id) {
            $this->documentService->delete($id);
        }

        $this->collectionService->delete($this->collectionId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('note.document.add creates a document and note.document.get returns it')]
    public function testAddAndGet(): void
    {
        $added = $this->documentService->add($this->collectionId, 'Onboarding guide', null, '# Hello');
        $documentId = $added->document()->id;
        $this->createdDocumentIds[] = $documentId;

        $this->assertGreaterThan(0, $documentId);
        $this->assertSame($this->collectionId, $added->document()->collectionId);

        $fetched = $this->documentService->get($documentId, (new DocumentSelectBuilder())->title()->markdown());
        $this->assertSame('Onboarding guide', $fetched->document()->title);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('note.document.update renames a document')]
    public function testUpdate(): void
    {
        $added = $this->documentService->add($this->collectionId, 'Original title');
        $documentId = $added->document()->id;
        $this->createdDocumentIds[] = $documentId;

        $updated = $this->documentService->update($documentId, ['title' => 'Updated title']);
        $this->assertSame('Updated title', $updated->document()->title);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('note.document.archive marks a document as archived')]
    public function testArchive(): void
    {
        $added = $this->documentService->add($this->collectionId, 'To be archived');
        $documentId = $added->document()->id;
        $this->createdDocumentIds[] = $documentId;

        $this->assertTrue($this->documentService->archive($documentId)->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('note.document.delete removes a document')]
    public function testDelete(): void
    {
        $added = $this->documentService->add($this->collectionId, 'To be deleted');
        $documentId = $added->document()->id;

        $this->assertTrue($this->documentService->delete($documentId)->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('note.document.field.list returns document field metadata')]
    public function testFieldList(): void
    {
        $fields = $this->documentService->fieldList()->getFields();
        $this->assertNotEmpty($fields);

        $names = array_map(static fn ($field) => $field->name, $fields);
        $this->assertContains('title', $names);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('note.document.tree.list returns the document tree of a knowledge base')]
    public function testTreeList(): void
    {
        $added = $this->documentService->add($this->collectionId, 'Tree root document');
        $documentId = $added->document()->id;
        $this->createdDocumentIds[] = $documentId;

        $tree = $this->documentService->treeList($this->collectionId);
        $ids = array_map(static fn ($item) => $item->id, $tree->getItems());
        $this->assertContains($documentId, $ids);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('note.document.tree.field.list returns document tree field metadata')]
    public function testTreeFieldList(): void
    {
        $fields = $this->documentService->treeFieldList()->getFields();
        $this->assertNotEmpty($fields);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('note.document.search.list performs a full-text search across documents')]
    public function testSearchList(): void
    {
        $uniqueTitle = 'UniqueSearchTerm' . time();
        $added = $this->documentService->add($this->collectionId, $uniqueTitle, null, 'searchable body');
        $this->createdDocumentIds[] = $added->document()->id;

        $result = $this->documentService->searchList($uniqueTitle);
        $this->assertIsArray($result->getItems());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('note.document.search.field.list returns document search field metadata')]
    public function testSearchFieldList(): void
    {
        $fields = $this->documentService->searchFieldList()->getFields();
        $this->assertNotEmpty($fields);
    }
}
