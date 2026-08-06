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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\DocumentContractor\Service;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Catalog\Document\Service\Document;
use Bitrix24\SDK\Services\Catalog\DocumentContractor\Service\Batch;
use Bitrix24\SDK\Services\Catalog\DocumentContractor\Service\DocumentContractor;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Batch::class)]
class BatchTest extends TestCase
{
    private DocumentContractor $documentContractorService;

    private Document $documentService;

    private CoreInterface $core;

    /**
     * @var int[]
     */
    private array $documentIds = [];

    /**
     * @var int[]
     */
    private array $contactIds = [];

    /**
     * @var int[]
     */
    private array $bindingIds = [];

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function setUp(): void
    {
        $serviceBuilder = Fabric::getServiceBuilder();
        $this->documentContractorService = $serviceBuilder->getCatalogScope()->documentContractor();
        $this->documentService = $serviceBuilder->getCatalogScope()->document();
        $this->core = Fabric::getCore();

        // a document can only have one contractor binding, so each binding needs its own document
        for ($i = 0; $i < 2; ++$i) {
            $documentId = $this->documentService->add([
                'docType' => 'A',
                'currency' => 'USD',
                'responsibleId' => 1,
                'title' => sprintf('test document contractor batch %s-%s', time(), $i),
            ])->document()->id;
            $this->documentIds[] = $documentId;

            $contactId = (int) $this->core->call('crm.contact.add', [
                'fields' => ['NAME' => sprintf('test contractor contact batch %s-%s', time(), $i)],
            ])->getResponseData()->getResult();
            $this->contactIds[] = $contactId;

            $this->bindingIds[] = $this->documentContractorService->add([
                'documentId' => $documentId,
                'entityTypeId' => 3,
                'entityId' => $contactId,
            ])->documentContractor()->id;
        }
    }

    #[\Override]
    protected function tearDown(): void
    {
        foreach ($this->documentIds as $documentId) {
            try {
                $this->documentService->delete($documentId);
            } catch (\Throwable) {
                // already removed, ignore
            }
        }

        foreach ($this->contactIds as $contactId) {
            try {
                $this->core->call('crm.contact.delete', ['id' => $contactId]);
            } catch (\Throwable) {
                // already removed, ignore
            }
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('test Batch::delete')]
    public function testDelete(): void
    {
        $this->assertNotEmpty($this->bindingIds);

        $deletedCount = 0;
        foreach ($this->documentContractorService->batch->delete($this->bindingIds) as $deletedItemResult) {
            $this->assertTrue($deletedItemResult->isSuccess());
            $deletedCount++;
        }

        $this->assertSame(count($this->bindingIds), $deletedCount);
    }
}
