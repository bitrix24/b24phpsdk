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
use Bitrix24\SDK\Services\Catalog\UserfieldDocument\Service\Batch;
use Bitrix24\SDK\Services\Catalog\UserfieldDocument\Service\UserfieldDocument;
use Bitrix24\SDK\Tests\Builders\Services\Catalog\UserfieldDocument\CatalogDocumentUserfieldFixture;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * Requires application (OAuth) credentials — see tests/ApplicationBridge/.
 */
#[CoversClass(Batch::class)]
class BatchTest extends TestCase
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
    #[TestDox('test Batch::update')]
    public function testUpdate(): void
    {
        $documentIds = [];
        for ($i = 0; $i < 2; ++$i) {
            $documentIds[] = $this->documentService->add([
                'docType' => 'A',
                'currency' => 'USD',
                'responsibleId' => 1,
                'title' => sprintf('batch userfield document %s-%s', time(), $i),
            ])->document()->id;
        }

        $this->createdDocumentIds = array_merge($this->createdDocumentIds, $documentIds);

        $updatePayload = [];
        foreach ($documentIds as $documentId) {
            $updatePayload[$documentId] = [
                'documentType' => 'A',
                $this->userfieldCode => sprintf('batch value %s', $documentId),
            ];
        }

        $updatedCount = 0;
        foreach ($this->userfieldDocumentService->batch->update($updatePayload) as $updatedItemResult) {
            $this->assertNotEmpty($updatedItemResult->document()->{$this->userfieldCode});
            ++$updatedCount;
        }

        $this->assertSame(2, $updatedCount);
    }
}
