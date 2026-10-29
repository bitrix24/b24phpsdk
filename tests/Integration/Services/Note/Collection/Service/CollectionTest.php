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

namespace Bitrix24\SDK\Tests\Integration\Services\Note\Collection\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\AuthForbiddenException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Note\Collection\Result\CollectionState;
use Bitrix24\SDK\Services\Note\Collection\Service\Collection;
use Bitrix24\SDK\Services\Note\Collection\Service\CollectionListCursor;
use Bitrix24\SDK\Services\Note\Collection\Service\CollectionListPagination;
use Bitrix24\SDK\Services\Note\Collection\Service\CollectionSelectBuilder;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Collection::class)]
class CollectionTest extends TestCase
{
    private Collection $collectionService;

    /**
     * @var int[]
     */
    private array $createdCollectionIds = [];

    #[\Override]
    protected function setUp(): void
    {
        $this->collectionService = Factory::getServiceBuilder()->getNoteScope()->collection();
    }

    #[\Override]
    protected function tearDown(): void
    {
        foreach ($this->createdCollectionIds as $id) {
            $this->collectionService->delete($id);
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('note.collection.add creates a knowledge base and note.collection.get returns it')]
    public function testAddAndGet(): void
    {
        $added = $this->collectionService->add('SDK test collection ' . time());
        $collectionId = $added->collection()->id;
        $this->createdCollectionIds[] = $collectionId;

        $this->assertGreaterThan(0, $collectionId);

        $fetched = $this->collectionService->get($collectionId, (new CollectionSelectBuilder())->name());
        $this->assertSame($collectionId, $fetched->collection()->id);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('note.collection.update renames a knowledge base')]
    public function testUpdate(): void
    {
        $added = $this->collectionService->add('SDK test collection ' . time());
        $collectionId = $added->collection()->id;
        $this->createdCollectionIds[] = $collectionId;

        $updated = $this->collectionService->update($collectionId, ['name' => 'SDK renamed collection']);
        $this->assertSame('SDK renamed collection', $updated->collection()->name);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('note.collection.archive marks a knowledge base as archived')]
    public function testArchive(): void
    {
        $added = $this->collectionService->add('SDK test collection ' . time());
        $collectionId = $added->collection()->id;
        $this->createdCollectionIds[] = $collectionId;

        $this->assertTrue($this->collectionService->archive($collectionId)->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('note.collection.delete removes a knowledge base')]
    public function testDelete(): void
    {
        $added = $this->collectionService->add('SDK test collection ' . time());
        $collectionId = $added->collection()->id;

        $this->assertTrue($this->collectionService->delete($collectionId)->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('note.collection.list returns knowledge bases and paginates via a cursor')]
    public function testListWithCursor(): void
    {
        $result = $this->collectionService->list(new CollectionListPagination(1));

        $collections = $result->getCollections();
        $this->assertNotEmpty($collections);
        $this->assertGreaterThan(0, $collections[0]->id);

        $nextCursor = $result->getNextCursor();
        if ($nextCursor instanceof CollectionListCursor) {
            $secondPage = $this->collectionService->list(new CollectionListPagination(1, $nextCursor));
            $this->assertIsArray($secondPage->getCollections());
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('note.collection.field.list returns collection field metadata')]
    public function testFieldList(): void
    {
        $fields = $this->collectionService->fieldList()->getFields();
        $this->assertNotEmpty($fields);

        $names = array_map(static fn ($field) => $field->name, $fields);
        $this->assertContains('name', $names);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('note.collection.field.get returns a single collection field descriptor')]
    public function testFieldGet(): void
    {
        $field = $this->collectionService->fieldGet('name')->field();
        $this->assertSame('name', $field->name);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('note.collection.move places a knowledge base between neighbours and returns the stored position')]
    public function testMove(): void
    {
        $collectionId = $this->addCollection();
        $initialPosition = $this->collectionService->get($collectionId)->collection()->position;

        $moved = $this->collectionService->move($collectionId, 1)->collection();
        $this->assertSame($collectionId, $moved->id);
        $this->assertSame(CollectionState::live, $moved->state);
        $this->assertNotSame($initialPosition, $moved->position);
        $this->assertSame($moved->position, $this->collectionService->get($collectionId)->collection()->position);

        $last = $this->collectionService->move($collectionId)->collection();
        $this->assertSame($collectionId, $last->id);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('note.collection.move refuses to reorder an archived knowledge base')]
    public function testMoveArchivedCollectionIsForbidden(): void
    {
        $collectionId = $this->addCollection();
        $this->collectionService->archive($collectionId);

        $this->expectException(AuthForbiddenException::class);
        $this->collectionService->move($collectionId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('note.collection.unarchive returns an archived knowledge base to the live state')]
    public function testArchiveAndUnarchive(): void
    {
        $collectionId = $this->addCollection();

        $this->assertTrue($this->collectionService->archive($collectionId)->isSuccess());
        $archived = $this->collectionService->get($collectionId)->collection();
        $this->assertTrue($archived->isArchived);
        $this->assertSame(CollectionState::archived, $archived->state);

        $this->assertTrue($this->collectionService->unarchive($collectionId)->isSuccess());
        $unarchived = $this->collectionService->get($collectionId)->collection();
        $this->assertFalse($unarchived->isArchived);
        $this->assertSame(CollectionState::live, $unarchived->state);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('note.collection.unarchive reports a knowledge base that is not archived as not found')]
    public function testUnarchiveLiveCollectionIsNotFound(): void
    {
        $collectionId = $this->addCollection();

        $this->expectException(BaseException::class);
        $this->expectExceptionMessageMatches('/entitynotfoundexception/i');
        $this->collectionService->unarchive($collectionId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('note.collection.restore returns a trashed knowledge base and a repeated call is safe')]
    public function testDeleteAndRestore(): void
    {
        $collectionId = $this->addCollection();

        $this->assertTrue($this->collectionService->delete($collectionId)->isSuccess());
        $this->assertSame(CollectionState::trashed, $this->collectionService->get($collectionId)->collection()->state);

        $this->assertTrue($this->collectionService->restore($collectionId)->isSuccess());
        $this->assertSame(CollectionState::live, $this->collectionService->get($collectionId)->collection()->state);

        $this->assertTrue($this->collectionService->restore($collectionId)->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('note.collection.restore reports an unknown knowledge base as not found')]
    public function testRestoreUnknownCollectionIsNotFound(): void
    {
        $this->expectException(BaseException::class);
        $this->expectExceptionMessageMatches('/entitynotfoundexception/i');
        $this->collectionService->restore(999_999_999);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    private function addCollection(): int
    {
        $collectionId = $this->collectionService->add('SDK test collection ' . time())->collection()->id;
        $this->createdCollectionIds[] = $collectionId;

        return $collectionId;
    }
}
