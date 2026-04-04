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
use Bitrix24\SDK\Services\CRM\Documentgenerator\Document\Result\DocumentItemResult;
use Bitrix24\SDK\Services\CRM\Documentgenerator\Document\Service\Document;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;
use Faker;

/**
 * Class DocumentTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\CRM\Documentgenerator\Document\Service
 */
#[CoversMethod(Document::class, 'add')]
#[CoversMethod(Document::class, 'delete')]
#[CoversMethod(Document::class, 'get')]
#[CoversMethod(Document::class, 'list')]
#[CoversMethod(Document::class, 'update')]
#[CoversMethod(Document::class, 'getFields')]
#[CoversMethod(Document::class, 'enablePublicUrl')]
#[CoversMethod(Document::class, 'upload')]
#[CoversMethod(Document::class, 'count')]
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\CRM\Documentgenerator\Document\Service\Document::class)]
class DocumentTest extends TestCase
{
    use CustomBitrix24Assertions;

    private Document $documentService;

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
     * Returns the first available template id from the list.
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
     * Helper: get a deal id for creating documents.
     *
     * @throws BaseException
     * @throws TransportException
     */
    private function createDeal(): int
    {
        $dealService = Factory::getServiceBuilder()->getCRMScope()->deal();
        return $dealService->add([
            'TITLE' => 'doc-test-' . $this->faker->uuid(),
        ])->getId();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetFields(): void
    {
        $templateId = $this->getFirstTemplateId();
        $dealId = $this->createDeal();

        $id = $this->documentService->add($templateId, 2, $dealId)->getId();

        $documentFieldsResult = $this->documentService->getFields($id);
        $fields = $documentFieldsResult->getFieldsDescription();

        self::assertIsArray($fields);
        self::assertNotEmpty($fields);

        // Cleanup
        $this->documentService->delete($id);
        Factory::getServiceBuilder()->getCRMScope()->deal()->delete($dealId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAdd(): void
    {
        $templateId = $this->getFirstTemplateId();
        $dealId = $this->createDeal();

        // entityTypeId = 2 is Deal in Bitrix24
        $id = $this->documentService->add($templateId, 2, $dealId)->getId();

        self::assertGreaterThanOrEqual(1, $id);

        // Cleanup
        $this->documentService->delete($id);
        Factory::getServiceBuilder()->getCRMScope()->deal()->delete($dealId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGet(): void
    {
        $templateId = $this->getFirstTemplateId();
        $dealId = $this->createDeal();

        $id = $this->documentService->add($templateId, 2, $dealId)->getId();

        $documentItemResult = $this->documentService->get($id)->document();
        self::assertInstanceOf(DocumentItemResult::class, $documentItemResult);
        self::assertEquals($id, $documentItemResult->id);

        // Cleanup
        $this->documentService->delete($id);
        Factory::getServiceBuilder()->getCRMScope()->deal()->delete($dealId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testList(): void
    {
        $templateId = $this->getFirstTemplateId();
        $dealId = $this->createDeal();

        $id = $this->documentService->add($templateId, 2, $dealId)->getId();

        $list = $this->documentService->list()->getDocuments();
        self::assertIsArray($list);
        self::assertGreaterThanOrEqual(1, count($list));

        // Cleanup
        $this->documentService->delete($id);
        Factory::getServiceBuilder()->getCRMScope()->deal()->delete($dealId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testUpdate(): void
    {
        $templateId = $this->getFirstTemplateId();
        $dealId = $this->createDeal();

        $id = $this->documentService->add($templateId, 2, $dealId)->getId();

        self::assertTrue(
            $this->documentService->update($id, [], 1)->isSuccess()
        );

        // Cleanup
        $this->documentService->delete($id);
        Factory::getServiceBuilder()->getCRMScope()->deal()->delete($dealId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelete(): void
    {
        $templateId = $this->getFirstTemplateId();
        $dealId = $this->createDeal();

        $id = $this->documentService->add($templateId, 2, $dealId)->getId();

        self::assertTrue($this->documentService->delete($id)->isSuccess());

        // Cleanup
        Factory::getServiceBuilder()->getCRMScope()->deal()->delete($dealId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testCount(): void
    {
        $templateId = $this->getFirstTemplateId();
        $dealId = $this->createDeal();

        $countBefore = $this->documentService->count();

        $id = $this->documentService->add($templateId, 2, $dealId)->getId();

        $countAfter = $this->documentService->count();
        self::assertEquals($countBefore + 1, $countAfter);

        // Cleanup
        $this->documentService->delete($id);
        Factory::getServiceBuilder()->getCRMScope()->deal()->delete($dealId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testEnablePublicUrl(): void
    {
        $templateId = $this->getFirstTemplateId();
        $dealId = $this->createDeal();

        $id = $this->documentService->add($templateId, 2, $dealId)->getId();

        $publicUrlResult = $this->documentService->enablePublicUrl($id);
        self::assertTrue($publicUrlResult->isSuccess());

        // Cleanup
        $this->documentService->delete($id);
        Factory::getServiceBuilder()->getCRMScope()->deal()->delete($dealId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testUpload(): void
    {
        $dealId = $this->createDeal();

        // Create a minimal content for upload (base64 encoded)
        $fileContent = base64_encode('Test document content');
        $fileName = 'test-upload-' . $this->faker->uuid() . '.pdf';

        $documentResult = $this->documentService->upload([
            'fileContent' => $fileContent,
            'fileName' => $fileName,
            'entityTypeId' => 2,         // entityTypeId = Deal
            'entityId' => $dealId,
            'title' => 'Test Upload Document',
            'number' => 'UP-' . $this->faker->randomNumber(5),
            'region' => 'uk',
        ]);
        $document = $documentResult->document();
        // upload creates a new document, verify a valid document is returned
        self::assertGreaterThanOrEqual(1, $document->id);
        self::assertInstanceOf(DocumentItemResult::class, $document);

        // Cleanup
        $this->documentService->delete($document->id);
        Factory::getServiceBuilder()->getCRMScope()->deal()->delete($dealId);
    }
}
