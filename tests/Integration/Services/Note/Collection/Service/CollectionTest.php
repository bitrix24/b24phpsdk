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
use Bitrix24\SDK\Core\Exceptions\TransportException;
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
}
