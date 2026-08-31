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

namespace Bitrix24\SDK\Tests\Integration\Services\Documentgenerator\Document\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Documentgenerator\Document\Result\DocumentItemResult;
use Bitrix24\SDK\Services\Documentgenerator\Document\Service\Document;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;
use Faker;

/**
 * Class DocumentTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Documentgenerator\Document\Service
 */
#[CoversMethod(Document::class, 'add')]
#[CoversMethod(Document::class, 'delete')]
#[CoversMethod(Document::class, 'get')]
#[CoversMethod(Document::class, 'list')]
#[CoversMethod(Document::class, 'update')]
#[CoversMethod(Document::class, 'getFields')]
#[CoversMethod(Document::class, 'enablePublicUrl')]
#[CoversMethod(Document::class, 'count')]
#[\PHPUnit\Framework\Attributes\CoversClass(Document::class)]
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
        $this->documentService = Fabric::getServiceBuilder()->getDocumentgeneratorScope()->document();
        $this->faker = Faker\Factory::create();
    }

    /**
     * Helper: get first available non-CRM documentgenerator template.
     * Returns array with keys: id, providerClassName
     *
     * @return array{id: int, providerClassName: string}
     *
     * @throws BaseException
     * @throws TransportException
     */
    private function getFirstTemplate(): array
    {
        $core = Fabric::getCore();
        $response = $core->call('documentgenerator.template.list', ['select' => ['id', 'providers']]);
        $result = $response->getResponseData()->getResult();
        $templates = $result['templates'] ?? [];

        self::assertNotEmpty($templates, 'At least one documentgenerator template must exist to run this test');

        $template = array_values($templates)[0];
        $providers = $template['providers'] ?? [];
        $providerClassName = empty($providers) ? 'Bitrix\DocumentGenerator\DataProvider\Rest' : (string)$providers[0];

        return [
            'id' => (int)$template['id'],
            'providerClassName' => $providerClassName,
        ];
    }

    /**
     * Helper: create a document for tests.
     *
     * @return array{id: int, templateInfo: array{id: int, providerClassName: string}}
     *
     * @throws BaseException
     * @throws TransportException
     */
    private function createDocument(): array
    {
        $templateInfo = $this->getFirstTemplate();
        $value = 'SDK_TEST_' . $this->faker->uuid();

        $id = $this->documentService->add(
            $templateInfo['id'],
            $templateInfo['providerClassName'],
            $value
        )->getId();

        return ['id' => $id, 'templateInfo' => $templateInfo];
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAdd(): void
    {
        $templateInfo = $this->getFirstTemplate();
        $value = 'SDK_TEST_' . $this->faker->uuid();

        $id = $this->documentService->add(
            $templateInfo['id'],
            $templateInfo['providerClassName'],
            $value
        )->getId();

        self::assertGreaterThanOrEqual(1, $id);

        // Cleanup
        $this->documentService->delete($id);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGet(): void
    {
        $doc = $this->createDocument();

        $documentItemResult = $this->documentService->get($doc['id'])->document();
        self::assertInstanceOf(DocumentItemResult::class, $documentItemResult);
        self::assertEquals($doc['id'], $documentItemResult->id);

        // Cleanup
        $this->documentService->delete($doc['id']);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testList(): void
    {
        $doc = $this->createDocument();

        $list = $this->documentService->list()->getDocuments();
        self::assertIsArray($list);
        self::assertGreaterThanOrEqual(1, count($list));

        // Cleanup
        $this->documentService->delete($doc['id']);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testUpdate(): void
    {
        $doc = $this->createDocument();

        self::assertTrue(
            $this->documentService->update($doc['id'], [], [], 1)->isSuccess()
        );

        // Cleanup
        $this->documentService->delete($doc['id']);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelete(): void
    {
        $doc = $this->createDocument();

        self::assertTrue($this->documentService->delete($doc['id'])->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testCount(): void
    {
        $countBefore = $this->documentService->count();

        $doc = $this->createDocument();

        $countAfter = $this->documentService->count();
        self::assertEquals($countBefore + 1, $countAfter);

        // Cleanup
        $this->documentService->delete($doc['id']);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetFields(): void
    {
        $doc = $this->createDocument();

        $documentFieldsResult = $this->documentService->getFields($doc['id']);
        $fields = $documentFieldsResult->getFieldsDescription();

        self::assertIsArray($fields);

        // Cleanup
        $this->documentService->delete($doc['id']);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testEnablePublicUrl(): void
    {
        $doc = $this->createDocument();

        $publicUrlResult = $this->documentService->enablePublicUrl($doc['id']);
        self::assertTrue($publicUrlResult->isSuccess());

        // Cleanup
        $this->documentService->delete($doc['id']);
    }
}



