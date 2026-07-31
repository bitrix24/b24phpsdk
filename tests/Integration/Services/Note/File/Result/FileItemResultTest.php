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

namespace Bitrix24\SDK\Tests\Integration\Services\Note\File\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Note\Collection\Service\Collection;
use Bitrix24\SDK\Services\Note\Document\Service\Document;
use Bitrix24\SDK\Services\Note\File\Result\FileItemResult;
use Bitrix24\SDK\Services\Note\File\Service\File;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(FileItemResult::class)]
class FileItemResultTest extends TestCase
{
    use CustomBitrix24Assertions;

    private Collection $collectionService;

    private Document $documentService;

    private File $fileService;

    private int $collectionId;

    private int $documentId;

    private int $fileId;

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

        $this->collectionId = $this->collectionService->add('SDK file annotation test ' . time())
            ->collection()->id;
        $this->documentId = $this->documentService->add($this->collectionId, 'File annotation test document')
            ->document()->id;
        $this->fileId = $this->fileService->add($this->documentId, 'note.txt', base64_encode('content'))
            ->file()->id;
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
    #[Test]
    #[TestDox('all fields in FileItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllFieldsAreAnnotated(): void
    {
        $rawItem = $this->fileService->get($this->fileId, $this->documentId)
            ->getCoreResponse()->getResponseData()->getResult()['item'];

        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($rawItem), FileItemResult::class);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('all fields in FileItemResult have valid type casting in magic getters')]
    public function testAllFieldsHasValidTypeCastingInMagicGetters(): void
    {
        $file = $this->fileService->get($this->fileId, $this->documentId)->file();
        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations($file, FileItemResult::class);
    }
}
