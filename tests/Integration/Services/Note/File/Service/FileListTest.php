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

namespace Bitrix24\SDK\Tests\Integration\Services\Note\File\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Services\Note\Collection\Service\Collection;
use Bitrix24\SDK\Services\Note\Document\Service\Document;
use Bitrix24\SDK\Services\Note\File\Result\FileItemResult;
use Bitrix24\SDK\Services\Note\File\Service\File;
use Bitrix24\SDK\Services\Note\File\Service\FileListPagination;
use Bitrix24\SDK\Services\Note\FileField\Service\FileField;
use Bitrix24\SDK\Tests\Integration\Factory;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(File::class)]
#[CoversClass(FileField::class)]
class FileListTest extends TestCase
{
    private Collection $collections;
    private Document $documents;
    private File $files;
    private FileField $fields;
    private ?int $collectionId = null;
    private ?int $documentId = null;
    private bool $documentDeleted = false;

    #[\Override]
    protected function setUp(): void
    {
        $scope = Factory::getServiceBuilder()->getNoteScope();
        $this->collections = $scope->collection();
        $this->documents = $scope->document();
        $this->files = $scope->file();
        $this->fields = $scope->fileField();
        $this->collectionId = $this->collections->add('SDK #688 ' . bin2hex(random_bytes(6)))->collection()->id;
        try {
            $this->documentId = $this->documents->add($this->collectionId, 'File listing test')->document()->id;
        } catch (\Throwable $exception) {
            $this->collections->delete($this->collectionId)->isSuccess();
            $this->collectionId = null;
            throw $exception;
        }
    }

    #[\Override]
    protected function tearDown(): void
    {
        try {
            if ($this->documentId !== null && !$this->documentDeleted) {
                $this->documents->delete($this->documentId)->isSuccess();
            }
        } finally {
            if ($this->collectionId !== null) {
                $this->collections->delete($this->collectionId)->isSuccess();
            }
        }
    }

    public function testEmptyDocumentHasNoFilesOrNextCursor(): void
    {
        $page = $this->files->list($this->documentId);
        self::assertSame([], $page->getFiles());
        self::assertNull($page->getNextCursor());
    }

    public function testCursorPaginationReturnsEveryUploadedFileOnce(): void
    {
        $uploaded = [];
        foreach (['first.txt', 'second.txt'] as $name) {
            $uploaded[] = $this->files->add($this->documentId, $name, base64_encode('SDK pagination fixture'))->file()->id;
        }

        $ids = [];
        $names = [];
        $cursor = null;
        for ($requests = 0; $requests < 5; ++$requests) {
            $page = $this->files->list($this->documentId, new FileListPagination(1, $cursor));
            $items = $page->getFiles();
            self::assertLessThanOrEqual(1, count($items));
            foreach ($items as $item) {
                self::assertInstanceOf(FileItemResult::class, $item);
                self::assertSame($this->documentId, $item->documentId);
                self::assertGreaterThan(0, $item->size);
                self::assertNotEmpty($item->mimeType);
                self::assertSame('file', $item->assetType);
                self::assertStringContainsString((string)$item->id, $item->assetMarkdown);
                self::assertInstanceOf(CarbonImmutable::class, $item->createdAt);
                $ids[] = $item->id;
                $names[] = $item->name;
            }

            $cursor = $page->getNextCursor();
            if ($cursor === null) {
                break;
            }
        }

        self::assertNull($cursor, 'Pagination must terminate within the bounded fixture traversal.');
        sort($uploaded);
        self::assertSame($uploaded, $ids, 'Files must be returned once each, in ascending ID order.');
        self::assertSame(['first.txt', 'second.txt'], $names);
    }

    public function testArchivedDocumentFilesRemainReadable(): void
    {
        $id = $this->files->add($this->documentId, 'archived.txt', base64_encode('archived fixture'))->file()->id;
        self::assertTrue($this->documents->archive($this->documentId)->isSuccess());
        $items = $this->files->list($this->documentId)->getFiles();
        self::assertCount(1, $items);
        self::assertSame($id, $items[0]->id);
    }

    public function testRecycledDocumentFilesRemainReadable(): void
    {
        $id = $this->files->add($this->documentId, 'recycled.txt', base64_encode('recycled fixture'))->file()->id;
        self::assertTrue($this->documents->delete($this->documentId)->isSuccess());
        $this->documentDeleted = true;
        $items = $this->files->list($this->documentId)->getFiles();
        self::assertCount(1, $items);
        self::assertSame($id, $items[0]->id);
    }

    public function testMissingDocumentPropagatesEntityNotFound(): void
    {
        $this->expectException(BaseException::class);
        $this->expectExceptionMessage('bitrix_rest_v3_exception_entitynotfoundexception');
        $this->files->list(2147483647)->getFiles();
    }

    public function testDedicatedFieldMetadataPreservesLegacyAccessors(): void
    {
        $fields = $this->fields->list()->getFieldsDescription();
        self::assertArrayHasKey('createdAt', $fields);
        $legacy = $this->files->fieldList()->getFieldsDescription();
        self::assertSame($legacy, $fields);
        self::assertSame('name', $this->fields->get('name')->field()->name);
        self::assertSame($this->files->fieldGet('name')->field()->type, $this->fields->get('name')->field()->type);
    }
}
