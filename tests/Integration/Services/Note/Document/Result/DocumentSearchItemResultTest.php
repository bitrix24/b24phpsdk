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

namespace Bitrix24\SDK\Tests\Integration\Services\Note\Document\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Note\Collection\Service\Collection;
use Bitrix24\SDK\Services\Note\Document\Result\DocumentSearchItemResult;
use Bitrix24\SDK\Services\Note\Document\Service\Document;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(DocumentSearchItemResult::class)]
class DocumentSearchItemResultTest extends TestCase
{
    use CustomBitrix24Assertions;

    private Collection $collectionService;

    private Document $documentService;

    private int $collectionId;

    private int $documentId;

    private string $searchTitle;

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

        $this->searchTitle = 'UniqueSearchAnnotationTerm' . time();
        $this->collectionId = $this->collectionService->add('SDK search annotation test ' . time())
            ->collection()->id;
        $this->documentId = $this->documentService->add($this->collectionId, $this->searchTitle, null, 'searchable body')
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
    #[Test]
    #[TestDox('all fields in DocumentSearchItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllFieldsAreAnnotated(): void
    {
        $items = $this->documentService->searchList($this->searchTitle)
            ->getCoreResponse()->getResponseData()->getResult()['items'];

        if ($items === []) {
            $this->markTestSkipped('Search index has not caught up with the freshly created document yet.');
        }

        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($items[0]), DocumentSearchItemResult::class);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('all fields in DocumentSearchItemResult have valid type casting in magic getters')]
    public function testAllFieldsHasValidTypeCastingInMagicGetters(): void
    {
        $items = $this->documentService->searchList($this->searchTitle)->getItems();

        if ($items === []) {
            $this->markTestSkipped('Search index has not caught up with the freshly created document yet.');
        }

        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations($items[0], DocumentSearchItemResult::class);
    }
}
