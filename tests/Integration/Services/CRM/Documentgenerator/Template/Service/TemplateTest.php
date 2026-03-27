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

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\Documentgenerator\Template\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\CRM\Documentgenerator\Template\Result\TemplateItemResult;
use Bitrix24\SDK\Services\CRM\Documentgenerator\Template\Service\Template;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;
use Faker;

/**
 * Class TemplateTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\CRM\Documentgenerator\Template\Service
 */
#[CoversMethod(Template::class, 'add')]
#[CoversMethod(Template::class, 'delete')]
#[CoversMethod(Template::class, 'get')]
#[CoversMethod(Template::class, 'list')]
#[CoversMethod(Template::class, 'update')]
#[CoversMethod(Template::class, 'getFields')]
#[CoversMethod(Template::class, 'count')]
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\CRM\Documentgenerator\Template\Service\Template::class)]
class TemplateTest extends TestCase
{
    use CustomBitrix24Assertions;

    private Template $templateService;

    private Faker\Generator $faker;

    /**
     * @throws InvalidArgumentException
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->templateService = Factory::getServiceBuilder()->getCRMScope()->documentgeneratorTemplate();
        $this->faker = Faker\Factory::create();
    }

    /**
     * Helper: creates a minimal .docx file content encoded as base64 for template upload.
     */
    private function createMinimalDocxBase64(): string
    {
        // Minimal valid .docx is a zip with basic structure.
        // For testing purposes we use a small base64-encoded .docx template.
        // This is a minimal valid docx file that Bitrix24 can accept.
        $tmpFile = tempnam(sys_get_temp_dir(), 'docx_');
        $zipArchive = new \ZipArchive();
        $zipArchive->open($tmpFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        $zipArchive->addFromString(
            '[Content_Types].xml',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
    <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
    <Default Extension="xml" ContentType="application/xml"/>
    <Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>
</Types>'
        );

        $zipArchive->addFromString(
            '_rels/.rels',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>
</Relationships>'
        );

        $zipArchive->addFromString(
            'word/document.xml',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
    <w:body>
        <w:p>
            <w:r>
                <w:t>Test template {Number}</w:t>
            </w:r>
        </w:p>
    </w:body>
</w:document>'
        );

        $zipArchive->close();

        $content = file_get_contents($tmpFile);
        unlink($tmpFile);

        return base64_encode($content);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetFields(): void
    {
        $templates = $this->templateService->list()->getTemplates();
        if ($templates === []) {
            self::markTestSkipped('No templates available for testing getFields()');
        }

        $firstTemplate = $templates[0];

        // entityTypeId = 2 is Deal in Bitrix24
        $templateFieldsResult = $this->templateService->getFields($firstTemplate->id, 2);
        $fields = $templateFieldsResult->getFieldsDescription();

        self::assertIsArray($fields);
        self::assertNotEmpty($fields);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testList(): void
    {
        $list = $this->templateService->list()->getTemplates();
        self::assertIsArray($list);
        // There should be at least system templates
        self::assertGreaterThanOrEqual(0, count($list));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGet(): void
    {
        $templates = $this->templateService->list()->getTemplates();
        if ($templates === []) {
            self::markTestSkipped('No templates available for testing get()');
        }

        $firstTemplate = $templates[0];
        $templateItemResult = $this->templateService->get($firstTemplate->id)->template();
        self::assertInstanceOf(TemplateItemResult::class, $templateItemResult);
        self::assertEquals($firstTemplate->id, $templateItemResult->id);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAdd(): void
    {
        $name = 'tpl-' . $this->faker->uuid();
        $fileContent = $this->createMinimalDocxBase64();

        $id = $this->templateService->add([
            'name' => $name,
            'file' => $fileContent,
            'numeratorId' => 0,
        ])->getId();

        self::assertGreaterThanOrEqual(1, $id);

        // Cleanup
        $this->templateService->delete($id);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testUpdate(): void
    {
        $name = 'tpl-' . $this->faker->uuid();
        $fileContent = $this->createMinimalDocxBase64();

        $id = $this->templateService->add([
            'name' => $name,
            'file' => $fileContent,
        ])->getId();

        $newName = $name . '-updated';
        self::assertTrue(
            $this->templateService->update($id, [
                'name' => $newName,
            ])->isSuccess()
        );

        self::assertEquals($newName, $this->templateService->get($id)->template()->name);

        // Cleanup
        $this->templateService->delete($id);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelete(): void
    {
        $fileContent = $this->createMinimalDocxBase64();

        $id = $this->templateService->add([
            'name' => 'tpl-' . $this->faker->uuid(),
            'file' => $fileContent,
        ])->getId();

        self::assertTrue($this->templateService->delete($id)->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testCount(): void
    {
        $countBefore = $this->templateService->count();

        $fileContent = $this->createMinimalDocxBase64();
        $id = $this->templateService->add([
            'name' => 'tpl-' . $this->faker->uuid(),
            'file' => $fileContent,
        ])->getId();

        $countAfter = $this->templateService->count();
        self::assertEquals($countBefore + 1, $countAfter);

        // Cleanup
        $this->templateService->delete($id);
    }
}
