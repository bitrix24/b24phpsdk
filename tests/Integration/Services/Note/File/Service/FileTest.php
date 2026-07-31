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
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Note\Collection\Service\Collection;
use Bitrix24\SDK\Services\Note\Document\Service\Document;
use Bitrix24\SDK\Services\Note\File\Service\File;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(File::class)]
class FileTest extends TestCase
{
    private Collection $collectionService;

    private Document $documentService;

    private File $fileService;

    private int $collectionId;

    private int $documentId;

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
        $this->fileService = $serviceBuilder->getNoteScope()->file();

        $this->collectionId = $this->collectionService->add('SDK file test collection ' . time())
            ->collection()->id;
        $this->documentId = $this->documentService->add($this->collectionId, 'File test document')
            ->document()->id;
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function tearDown(): void
    {
        $this->documentService->delete($this->documentId);
        $this->collectionService->delete($this->collectionId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('note.file.add uploads a file and note.file.get returns it')]
    public function testAddAndGet(): void
    {
        $content = base64_encode('plain text file content');
        $added = $this->fileService->add($this->documentId, 'note.txt', $content);
        $fileId = $added->file()->id;

        $this->assertGreaterThan(0, $fileId);
        $this->assertSame($this->documentId, $added->file()->documentId);

        $fetched = $this->fileService->get($fileId, $this->documentId);
        $this->assertSame($fileId, $fetched->file()->id);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('note.file.field.list returns file field metadata')]
    public function testFieldList(): void
    {
        $fields = $this->fileService->fieldList()->getFields();
        $this->assertNotEmpty($fields);

        $names = array_map(static fn ($field) => $field->name, $fields);
        $this->assertContains('name', $names);
    }
}
