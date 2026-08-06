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
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Batch::class)]
class BatchTest extends TestCase
{
    private DocumentContractor $documentContractorService;

    private Document $documentService;

    private CoreInterface $core;

    private int $documentId;

    /**
     * @var int[]
     */
    private array $contactIds = [];

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function setUp(): void
    {
        $serviceBuilder = Factory::getServiceBuilder();
        $this->documentContractorService = $serviceBuilder->getCatalogScope()->documentContractor();
        $this->documentService = $serviceBuilder->getCatalogScope()->document();
        $this->core = Factory::getCore();

        $this->documentId = $this->documentService->add([
            'docType' => 'A',
            'currency' => 'USD',
            'responsibleId' => 1,
            'title' => sprintf('test document contractor batch %s', time()),
        ])->document()->id;

        for ($i = 0; $i < 2; ++$i) {
            $contactId = (int) $this->core->call('crm.contact.add', [
                'fields' => ['NAME' => sprintf('test contractor contact batch %s-%s', time(), $i)],
            ])->getResponseData()->getResult();
            $this->contactIds[] = $contactId;

            $this->documentContractorService->add([
                'documentId' => $this->documentId,
                'entityTypeId' => 3,
                'entityId' => $contactId,
            ]);
        }
    }

    #[\Override]
    protected function tearDown(): void
    {
        try {
            $this->documentService->delete($this->documentId);
        } catch (\Throwable) {
            // already removed, ignore
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
        $listResult = $this->documentContractorService->list([], ['documentId' => $this->documentId]);
        $bindingIds = array_map(
            static fn ($binding): int => $binding->id,
            $listResult->getDocumentContractors()
        );
        $this->assertNotEmpty($bindingIds);

        $deletedCount = 0;
        foreach ($this->documentContractorService->batch->delete($bindingIds) as $deletedItemResult) {
            $this->assertTrue($deletedItemResult->isSuccess());
            $deletedCount++;
        }

        $this->assertSame(count($bindingIds), $deletedCount);
    }
}
