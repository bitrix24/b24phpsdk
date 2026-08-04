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
use Bitrix24\SDK\Services\Catalog\DocumentElement\Service\Batch;
use Bitrix24\SDK\Services\Catalog\DocumentElement\Service\DocumentElement;
use Bitrix24\SDK\Services\Catalog\Product\Service\Product;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Batch::class)]
class BatchTest extends TestCase
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
        $serviceBuilder = Fabric::getServiceBuilder();
        $this->documentElementService = $serviceBuilder->getCatalogScope()->documentElement();
        $this->documentService = $serviceBuilder->getCatalogScope()->document();
        $this->productService = $serviceBuilder->getCatalogScope()->product();

        $catalogService = $serviceBuilder->getCatalogScope()->catalog();
        $iblockId = $catalogService->list([], [], [], 1)->getCatalogs()[0]->iblockId;

        $this->productId = $this->productService->add([
            'iblockId' => $iblockId,
            'name' => sprintf('test batch document element product %s', time()),
        ])->product()->id;

        $this->documentId = $this->documentService->add([
            'docType' => 'A',
            'currency' => 'USD',
            'responsibleId' => 1,
            'title' => sprintf('test batch document for element %s', time()),
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
    #[TestDox('test Batch::add, Batch::update, Batch::delete')]
    public function testAddUpdateDelete(): void
    {
        $addedIds = [];
        foreach ($this->documentElementService->batch->add([
            ['docId' => $this->documentId, 'elementId' => $this->productId, 'storeTo' => 0, 'amount' => 3],
        ]) as $addedItemResult) {
            $addedIds[] = $addedItemResult->documentElement()->id;
        }

        $this->assertCount(1, $addedIds);

        $updatePayload = [];
        foreach ($addedIds as $addedId) {
            $updatePayload[$addedId] = ['amount' => 6];
        }

        $updatedCount = 0;
        foreach ($this->documentElementService->batch->update($updatePayload) as $updatedItemResult) {
            $this->assertEqualsWithDelta(6.0, $updatedItemResult->documentElement()->amount, PHP_FLOAT_EPSILON);
            $updatedCount++;
        }

        $this->assertSame(1, $updatedCount);

        $deletedCount = 0;
        foreach ($this->documentElementService->batch->delete($addedIds) as $deletedItemResult) {
            $this->assertTrue($deletedItemResult->isSuccess());
            $deletedCount++;
        }

        $this->assertSame(1, $deletedCount);
    }
}
