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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\DocumentElement\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Catalog\Document\Service\Document;
use Bitrix24\SDK\Services\Catalog\DocumentElement\Service\DocumentElement;
use Bitrix24\SDK\Services\Catalog\Product\Service\Product;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(DocumentElement::class)]
class DocumentElementTest extends TestCase
{
    private DocumentElement $documentElementService;

    private Document $documentService;

    private Product $productService;

    private int $documentId;

    private int $productId;

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function setUp(): void
    {
        $serviceBuilder = Factory::getServiceBuilder();
        $this->documentElementService = $serviceBuilder->getCatalogScope()->documentElement();
        $this->documentService = $serviceBuilder->getCatalogScope()->document();
        $this->productService = $serviceBuilder->getCatalogScope()->product();

        $catalogService = $serviceBuilder->getCatalogScope()->catalog();
        $iblockId = $catalogService->list([], [], [], 1)->getCatalogs()[0]->iblockId;

        $this->productId = $this->productService->add([
            'iblockId' => $iblockId,
            'name' => sprintf('test document element product %s', time()),
        ])->product()->id;

        $this->documentId = $this->documentService->add([
            'docType' => 'A',
            'currency' => 'USD',
            'responsibleId' => 1,
            'title' => sprintf('test document for element %s', time()),
        ])->document()->id;
    }

    #[\Override]
    protected function tearDown(): void
    {
        try {
            $this->documentService->delete($this->documentId);
        } catch (\Throwable) {
            // already removed, ignore
        }

        try {
            $this->productService->delete($this->productId);
        } catch (\Throwable) {
            // already removed, ignore
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('test DocumentElement::add, DocumentElement::update, DocumentElement::list, DocumentElement::delete')]
    public function testAddUpdateListDelete(): void
    {
        $addResult = $this->documentElementService->add([
            'docId' => $this->documentId,
            'elementId' => $this->productId,
            'storeTo' => 0,
            'amount' => 5,
            'purchasingPrice' => 10.5,
        ]);
        $documentElementId = $addResult->documentElement()->id;

        $this->assertSame($this->documentId, $addResult->documentElement()->docId);
        $this->assertSame($this->productId, $addResult->documentElement()->elementId);

        $updateResult = $this->documentElementService->update($documentElementId, ['amount' => 8]);
        $this->assertEqualsWithDelta(8.0, $updateResult->documentElement()->amount, PHP_FLOAT_EPSILON);

        $listResult = $this->documentElementService->list([], ['docId' => $this->documentId]);
        $this->assertCount(1, $listResult->getDocumentElements());

        $this->assertTrue($this->documentElementService->delete($documentElementId)->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('test DocumentElement::getFields')]
    public function testGetFields(): void
    {
        $this->assertIsArray($this->documentElementService->getFields()->getFieldsDescription());
    }
}
