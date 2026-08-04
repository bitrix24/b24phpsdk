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
use Bitrix24\SDK\Services\Catalog\Document\Service\Batch;
use Bitrix24\SDK\Services\Catalog\Document\Service\Document;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Batch::class)]
class BatchTest extends TestCase
{
    private Document $documentService;

    #[\Override]
    protected function setUp(): void
    {
        $this->documentService = Fabric::getServiceBuilder(true)->getCatalogScope()->document();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('test Batch::add, Batch::update, Batch::delete')]
    public function testAddUpdateDelete(): void
    {
        $addedIds = [];
        foreach ($this->documentService->batch->add([
            ['docType' => 'A', 'currency' => 'USD', 'responsibleId' => 1, 'title' => sprintf('batch document %s', time())],
        ]) as $addedItemResult) {
            $addedIds[] = $addedItemResult->document()->id;
        }

        $this->assertCount(1, $addedIds);

        $updatePayload = [];
        foreach ($addedIds as $addedId) {
            $updatePayload[$addedId] = ['title' => 'updated batch document'];
        }

        $updatedCount = 0;
        foreach ($this->documentService->batch->update($updatePayload) as $updatedItemResult) {
            $this->assertSame('updated batch document', $updatedItemResult->document()->title);
            $updatedCount++;
        }

        $this->assertSame(1, $updatedCount);

        $deletedCount = 0;
        foreach ($this->documentService->batch->delete($addedIds) as $deletedItemResult) {
            $this->assertTrue($deletedItemResult->isSuccess());
            $deletedCount++;
        }

        $this->assertSame(1, $deletedCount);
    }
}
