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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\UserfieldDocument\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Catalog\Document\Service\Document;
use Bitrix24\SDK\Services\Catalog\UserfieldDocument\Service\UserfieldDocument;
use Bitrix24\SDK\Tests\Builders\Services\Catalog\UserfieldDocument\CatalogDocumentUserfieldFixture;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * Requires application (OAuth) credentials — see tests/ApplicationBridge/ — because discovering /
 * creating a catalog-module userfield to exercise fieldN read/write needs userfieldconfig.*, which
 * a plain incoming webhook is not permitted to call.
 */
#[CoversClass(UserfieldDocument::class)]
class UserfieldDocumentTest extends TestCase
{
    private UserfieldDocument $userfieldDocumentService;

    private Document $documentService;

    private string $userfieldCode;

    /**
     * @var int[]
     */
    private array $createdDocumentIds = [];

    #[\Override]
    protected function setUp(): void
    {
        $serviceBuilder = Factory::getServiceBuilder(true);
        $this->userfieldDocumentService = $serviceBuilder->getCatalogScope()->userfieldDocument();
        $this->documentService = $serviceBuilder->getCatalogScope()->document();
        $this->userfieldCode = CatalogDocumentUserfieldFixture::getOrCreateFieldCode(Factory::getCore(true));
    }

    #[\Override]
    protected function tearDown(): void
    {
        foreach ($this->createdDocumentIds as $documentId) {
            try {
                $this->documentService->delete($documentId);
            } catch (\Throwable) {
                // already removed, ignore
            }
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('test UserfieldDocument::update, UserfieldDocument::list')]
    public function testUpdateAndList(): void
    {
        $documentId = $this->createDocument();
        $fieldValue = sprintf('test userfield value %s', time());

        $updateResult = $this->userfieldDocumentService->update($documentId, [
            'documentType' => 'A',
            $this->userfieldCode => $fieldValue,
        ]);
        $this->assertSame($documentId, $updateResult->document()->documentId);
        $this->assertSame($fieldValue, $updateResult->document()->{$this->userfieldCode});

        $listResult = $this->userfieldDocumentService->list(
            ['documentType', 'documentId', $this->userfieldCode],
            ['documentType' => 'A', 'documentId' => $documentId]
        );
        $documents = $listResult->getDocuments();
        $this->assertCount(1, $documents);
        $this->assertSame($fieldValue, $documents[0]->{$this->userfieldCode});
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    private function createDocument(): int
    {
        $documentId = $this->documentService->add([
            'docType' => 'A',
            'currency' => 'USD',
            'responsibleId' => 1,
            'title' => sprintf('test userfield document %s', time()),
        ])->document()->id;
        $this->createdDocumentIds[] = $documentId;

        return $documentId;
    }
}
