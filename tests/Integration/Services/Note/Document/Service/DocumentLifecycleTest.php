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
use Bitrix24\SDK\Services\Note\Document\Result\DocumentItemResult;
use Bitrix24\SDK\Services\Note\Document\Result\DocumentState;
use Bitrix24\SDK\Services\Note\Document\Service\Document;
use Bitrix24\SDK\Services\Note\Document\Service\DocumentFilter;
use Bitrix24\SDK\Services\Note\Document\Service\DocumentListPagination;
use Bitrix24\SDK\Services\Note\Document\Service\DocumentSelectBuilder;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * note.document.list / move / restore / unarchive (REST v3, October 2026 additions).
 */
#[CoversClass(Document::class)]
class DocumentLifecycleTest extends TestCase
{
    private Collection $collectionService;

    private Document $documentService;

    /**
     * @var int[]
     */
    private array $createdCollectionIds = [];

    /**
     * @var int[]
     */
    private array $createdDocumentIds = [];

    #[\Override]
    protected function setUp(): void
    {
        $serviceBuilder = Factory::getServiceBuilder();
        $this->collectionService = $serviceBuilder->getNoteScope()->collection();
        $this->documentService = $serviceBuilder->getNoteScope()->document();
    }

    #[\Override]
    protected function tearDown(): void
    {
        foreach ($this->createdDocumentIds as $id) {
            try {
                $this->documentService->delete($id);
            } catch (BaseException) {
                // already in the recycle bin or removed together with its parent
            }
        }

        foreach ($this->createdCollectionIds as $id) {
            $this->collectionService->delete($id);
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('note.document.list returns live documents of a collection without markdown')]
    public function testList(): void
    {
        $collectionId = $this->addCollection();
        $documentId = $this->addDocument($collectionId, 'Listed document');

        $result = $this->documentService->list((new DocumentFilter())->collectionId()->eq($collectionId));
        $documents = $result->getDocuments();

        $this->assertSame([$documentId], array_map(static fn (DocumentItemResult $item): int => $item->id, $documents));
        $this->assertSame(DocumentState::live, $documents[0]->state);
        $this->assertFalse($documents[0]->isArchived);
        $this->assertFalse($documents[0]->isTrashed);
        $this->assertFalse($documents[0]->isOrphan);
        $this->assertArrayNotHasKey('markdown', $result->getCoreResponse()->getResponseData()->getResult()['items'][0]);
        $this->assertNull($result->getNextCursor());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('note.document.list walks every page until nextCursor is null')]
    public function testListWalksAllPagesWithCursor(): void
    {
        $collectionId = $this->addCollection();
        $expected = [
            $this->addDocument($collectionId, 'Page 1'),
            $this->addDocument($collectionId, 'Page 2'),
            $this->addDocument($collectionId, 'Page 3'),
        ];

        $filter = (new DocumentFilter())->collectionId()->eq($collectionId);
        $select = (new DocumentSelectBuilder())->title();
        $seen = [];
        $cursor = null;
        $calls = 0;
        do {
            $page = $this->documentService->list($filter, $select, new DocumentListPagination(1, $cursor));
            foreach ($page->getDocuments() as $document) {
                $seen[] = $document->id;
            }

            $cursor = $page->getNextCursor();
            $calls++;
        } while ($cursor !== null && $calls < 20);

        $this->assertNull($cursor, 'cursor walk did not finish');
        $this->assertGreaterThanOrEqual(3, $calls);
        sort($expected);
        sort($seen);
        $this->assertSame($expected, $seen);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('note.document.list returns archived and trashed documents only when asked by state')]
    public function testListFiltersByState(): void
    {
        $collectionId = $this->addCollection();
        $liveId = $this->addDocument($collectionId, 'Live');
        $archivedId = $this->addDocument($collectionId, 'Archived');
        $trashedId = $this->addDocument($collectionId, 'Trashed');
        $this->documentService->archive($archivedId);
        $this->documentService->delete($trashedId);

        $this->assertSame([$liveId], $this->listIds($collectionId));
        $this->assertSame([$archivedId], $this->listIds($collectionId, [DocumentState::archived]));
        $this->assertSame([$trashedId], $this->listIds($collectionId, [DocumentState::trashed]));

        $filter = new DocumentFilter();
        $filter->collectionId()->eq($collectionId);
        $filter->state()->in([DocumentState::live->value, DocumentState::archived->value, DocumentState::trashed->value]);
        $all = $this->documentService->list($filter)->getDocuments();
        $states = [];
        foreach ($all as $document) {
            $states[$document->id] = $document->state;
        }

        $this->assertSame(DocumentState::live, $states[$liveId]);
        $this->assertSame(DocumentState::archived, $states[$archivedId]);
        $this->assertSame(DocumentState::trashed, $states[$trashedId]);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('note.document.move moves a document with its children to another collection')]
    public function testMoveWithChildren(): void
    {
        $sourceId = $this->addCollection();
        $targetId = $this->addCollection();
        $parentId = $this->addDocument($sourceId, 'Parent');
        $childId = $this->addDocument($sourceId, 'Child', $parentId);

        $moved = $this->documentService->move($parentId, $targetId)->document();

        $this->assertSame($parentId, $moved->id);
        $this->assertSame($targetId, $moved->collectionId);
        $this->assertNull($moved->parentId);

        $child = $this->documentService->get($childId)->document();
        $this->assertSame($targetId, $child->collectionId);
        $this->assertSame($parentId, $child->parentId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('note.document.move places a document under another parent and returns the saved position')]
    public function testMoveUnderAnotherParent(): void
    {
        $collectionId = $this->addCollection();
        $parentId = $this->addDocument($collectionId, 'New parent');
        $documentId = $this->addDocument($collectionId, 'Moved');

        $moved = $this->documentService->move($documentId, null, $parentId, 1)->document();

        $this->assertSame($collectionId, $moved->collectionId);
        $this->assertSame($parentId, $moved->parentId);
        $this->assertIsInt($moved->position);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('note.document.unarchive returns an archived document to the tree')]
    public function testUnarchive(): void
    {
        $collectionId = $this->addCollection();
        $documentId = $this->addDocument($collectionId, 'Archived then returned');
        $this->documentService->archive($documentId);

        $this->assertTrue($this->documentService->unarchive($documentId)->isSuccess());

        $document = $this->documentService->get($documentId)->document();
        $this->assertSame(DocumentState::live, $document->state);
        $this->assertFalse($document->isArchived);
    }

    /**
     * @throws TransportException
     */
    #[TestDox('note.document.unarchive refuses a document that is not archived')]
    public function testUnarchiveNotArchivedThrows(): void
    {
        $documentId = $this->addDocument($this->addCollection(), 'Never archived');

        $this->expectException(BaseException::class);
        $this->documentService->unarchive($documentId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('note.document.restore brings a deleted document and its trashed children back from the recycle bin')]
    public function testRestoreFromRecycleBin(): void
    {
        $collectionId = $this->addCollection();
        $parentId = $this->addDocument($collectionId, 'Deleted parent');
        $childId = $this->addDocument($collectionId, 'Deleted child', $parentId);
        $this->documentService->delete($parentId);
        $this->assertSame(DocumentState::trashed, $this->documentService->get($childId)->document()->state);

        $restored = $this->documentService->restore($parentId)->document();

        $this->assertSame($parentId, $restored->id);
        $this->assertSame(DocumentState::live, $restored->state);
        $this->assertFalse($restored->isTrashed);

        // Live portal (2026-10-28): unlike the docs, the child stays trashed and is restored on its own.
        $child = $this->documentService->restore($childId)->document();
        $this->assertSame(DocumentState::live, $child->state);
        $this->assertSame($parentId, $child->parentId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('note.document.restore sends a document to another collection')]
    public function testRestoreToAnotherCollection(): void
    {
        $sourceId = $this->addCollection();
        $targetId = $this->addCollection();
        $documentId = $this->addDocument($sourceId, 'Restored elsewhere');
        $this->documentService->delete($documentId);

        $restored = $this->documentService->restore($documentId, $targetId)->document();

        $this->assertSame($targetId, $restored->collectionId);
        $this->assertSame(DocumentState::live, $restored->state);
    }

    /**
     * @throws TransportException
     */
    #[TestDox('note.document.restore refuses a document that is not in the recycle bin')]
    public function testRestoreNotTrashedThrows(): void
    {
        $documentId = $this->addDocument($this->addCollection(), 'Never deleted');

        $this->expectException(BaseException::class);
        $this->documentService->restore($documentId);
    }

    /**
     * @param DocumentState[] $states
     * @return int[]
     * @throws BaseException
     * @throws TransportException
     */
    private function listIds(int $collectionId, array $states = []): array
    {
        $filter = new DocumentFilter();
        $filter->collectionId()->eq($collectionId);
        if ($states !== []) {
            $filter->state()->in(array_map(static fn (DocumentState $state): string => $state->value, $states));
        }

        return array_map(
            static fn (DocumentItemResult $item): int => $item->id,
            $this->documentService->list($filter)->getDocuments()
        );
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    private function addCollection(): int
    {
        $id = $this->collectionService->add('SDK document lifecycle test ' . microtime(true))->collection()->id;
        $this->createdCollectionIds[] = $id;

        return $id;
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    private function addDocument(int $collectionId, string $title, ?int $parentId = null): int
    {
        $id = $this->documentService->add($collectionId, $title, $parentId)->document()->id;
        $this->createdDocumentIds[] = $id;

        return $id;
    }
}
