<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <titarx@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\Documentgenerator\Document\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\CRM\Documentgenerator\Document\Service\Document;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\TestCase;
use Faker;

/**
 * Class BatchTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\CRM\Documentgenerator\Document\Service
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\CRM\Documentgenerator\Document\Service\Batch::class)]
class BatchTest extends TestCase
{
    protected Document $documentService;

    private Faker\Generator $faker;

    /**
     * @throws InvalidArgumentException
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->documentService = Factory::getServiceBuilder()->getCRMScope()->documentgeneratorDocument();
        $this->faker = Faker\Factory::create();
    }

    /**
     * Helper: get a template id for creating documents.
     *
     * @throws BaseException
     * @throws TransportException
     */
    private function getFirstTemplateId(): int
    {
        $templateService = Factory::getServiceBuilder()->getCRMScope()->documentgeneratorTemplate();
        $templates = $templateService->list()->getTemplates();
        self::assertNotEmpty($templates, 'At least one template must exist to create a document');

        return $templates[0]->id;
    }

    /**
     * Helper: create a deal for document tests.
     *
     * @throws BaseException
     * @throws TransportException
     */
    private function createDeal(): int
    {
        $dealService = Factory::getServiceBuilder()->getCRMScope()->deal();
        return $dealService->add([
            'TITLE' => 'doc-batch-test-' . $this->faker->uuid(),
        ])->getId();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch list documents')]
    public function testBatchList(): void
    {
        $templateId = $this->getFirstTemplateId();
        $dealId = $this->createDeal();

        $id = $this->documentService->add($templateId, 2, $dealId)->getId();

        $cnt = 0;
        foreach ($this->documentService->batch->list(1) as $item) {
            $cnt++;
        }

        self::assertGreaterThanOrEqual(1, $cnt);

        // Cleanup
        $this->documentService->delete($id);
        Factory::getServiceBuilder()->getCRMScope()->deal()->delete($dealId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch delete documents')]
    public function testBatchDelete(): void
    {
        $templateId = $this->getFirstTemplateId();
        $ids = [];
        $dealIds = [];

        for ($i = 1; $i <= 3; $i++) {
            $dealId = $this->createDeal();
            $dealIds[] = $dealId;
            $ids[] = $this->documentService->add($templateId, 2, $dealId)->getId();
        }

        $delCnt = 0;
        foreach ($this->documentService->batch->delete($ids) as $deleted) {
            $delCnt++;
        }

        self::assertEquals(count($ids), $delCnt);

        // Cleanup deals
        $dealService = Factory::getServiceBuilder()->getCRMScope()->deal();
        foreach ($dealIds as $dealId) {
            $dealService->delete($dealId);
        }
    }
}

