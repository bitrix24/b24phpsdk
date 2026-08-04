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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\Document\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Catalog\Document\Service\Document;
use Bitrix24\SDK\Services\Catalog\DocumentElement\Service\DocumentElement;
use Bitrix24\SDK\Services\Catalog\Product\Service\Product;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Document::class)]
class DocumentTest extends TestCase
{
    private Document $documentService;

    private DocumentElement $documentElementService;

    private Product $productService;

    /**
     * @var int[]
     */
    private array $createdDocumentIds = [];

    /**
     * @var int[]
     */
    private array $createdProductIds = [];

    #[\Override]
    protected function setUp(): void
    {
        $serviceBuilder = Fabric::getServiceBuilder(true);
        $this->documentService = $serviceBuilder->getCatalogScope()->document();
        $this->documentElementService = $serviceBuilder->getCatalogScope()->documentElement();
        $this->productService = $serviceBuilder->getCatalogScope()->product();
    }

    #[\Override]
    protected function tearDown(): void
    {
        foreach ($this->createdDocumentIds as $documentId) {
            try {
                $this->documentService->cancel($documentId);
            } catch (\Throwable) {
                // document may not be conducted, ignore
            }

            try {
                $this->documentService->delete($documentId);
            } catch (\Throwable) {
                // already removed, ignore
            }
        }

        foreach ($this->createdProductIds as $productId) {
            try {
                $this->productService->delete($productId);
            } catch (\Throwable) {
                // already removed, ignore
            }
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('test Document::add, Document::update, Document::list, Document::delete')]
    public function testAddUpdateListDelete(): void
    {
        $title = sprintf('test document %s', time());
        $addResult = $this->documentService->add([
            'docType' => 'A',
            'currency' => 'USD',
            'responsibleId' => 1,
            'title' => $title,
        ]);
        $documentId = $addResult->document()->id;
        $this->createdDocumentIds[] = $documentId;

        $this->assertSame($title, $addResult->document()->title);
        $this->assertSame('A', $addResult->document()->docType);

        $updatedTitle = sprintf('updated test document %s', time());
        $updateResult = $this->documentService->update($documentId, ['title' => $updatedTitle]);
        $this->assertSame($updatedTitle, $updateResult->document()->title);

        $listResult = $this->documentService->list([], ['id' => $documentId]);
        $this->assertCount(1, $listResult->getDocuments());

        $this->assertTrue($this->documentService->delete($documentId)->isSuccess());
        $this->createdDocumentIds = array_diff($this->createdDocumentIds, [$documentId]);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('test Document::deleteList')]
    public function testDeleteList(): void
    {
        $documentIds = [];
        for ($i = 0; $i < 2; ++$i) {
            $documentIds[] = $this->documentService->add([
                'docType' => 'A',
                'currency' => 'USD',
                'responsibleId' => 1,
                'title' => sprintf('test document deleteList %s-%s', time(), $i),
            ])->document()->id;
        }

        $this->createdDocumentIds = array_merge($this->createdDocumentIds, $documentIds);

        $this->assertTrue($this->documentService->deleteList($documentIds)->isSuccess());
        $this->createdDocumentIds = array_values(array_diff($this->createdDocumentIds, $documentIds));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('test Document::getFields')]
    public function testGetFields(): void
    {
        $this->assertIsArray($this->documentService->getFields()->getFieldsDescription());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('test Document::modeStatus')]
    public function testModeStatus(): void
    {
        $this->assertIsBool($this->documentService->modeStatus()->isEnabled());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('test Document::conduct, Document::cancel')]
    public function testConductCancel(): void
    {
        $documentId = $this->createDocumentWithElement('test document conduct');

        $this->assertTrue($this->documentService->conduct($documentId)->isSuccess());
        $this->assertTrue($this->documentService->cancel($documentId)->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('test Document::conductList, Document::cancelList')]
    public function testConductListCancelList(): void
    {
        $documentIds = [
            $this->createDocumentWithElement('test document conductList 0'),
            $this->createDocumentWithElement('test document conductList 1'),
        ];

        $this->assertTrue($this->documentService->conductList($documentIds)->isSuccess());
        $this->assertTrue($this->documentService->cancelList($documentIds)->isSuccess());
    }

    /**
     * Creates a stock-taking document ('S') with one line item so it can be conducted.
     * docType 'S' is used because it does not require a supplier, unlike docType 'A' (goods receipt).
     *
     * @throws BaseException
     * @throws TransportException
     */
    private function createDocumentWithElement(string $title): int
    {
        $iblockId = Fabric::getServiceBuilder(true)->getCatalogScope()->catalog()
            ->list([], [], [], 1)->getCatalogs()[0]->iblockId;

        $stores = Fabric::getCore(true)->call('catalog.store.list', ['select' => ['id'], 'filter' => ['active' => 'Y']])
            ->getResponseData()->getResult();
        $storeId = $stores['stores'][0]['id'];

        $productId = $this->productService->add([
            'iblockId' => $iblockId,
            'name' => sprintf('%s product %s', $title, time()),
        ])->product()->id;
        $this->createdProductIds[] = $productId;

        $documentId = $this->documentService->add([
            'docType' => 'S',
            'currency' => 'USD',
            'responsibleId' => 1,
            'title' => sprintf('%s %s', $title, time()),
        ])->document()->id;
        $this->createdDocumentIds[] = $documentId;

        $this->documentElementService->add([
            'docId' => $documentId,
            'elementId' => $productId,
            'storeTo' => $storeId,
            'amount' => 1,
        ]);

        return $documentId;
    }
}
