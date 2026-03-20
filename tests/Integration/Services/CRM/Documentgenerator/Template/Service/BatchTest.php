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
use Bitrix24\SDK\Services\CRM\Documentgenerator\Template\Service\Template;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\TestCase;
use Faker;

/**
 * Class BatchTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\CRM\Documentgenerator\Template\Service
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\CRM\Documentgenerator\Template\Service\Batch::class)]
class BatchTest extends TestCase
{
    protected Template $templateService;

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
        $tmpFile = tempnam(sys_get_temp_dir(), 'docx_');
        $zip = new \ZipArchive();
        $zip->open($tmpFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
    <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
    <Default Extension="xml" ContentType="application/xml"/>
    <Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>
</Types>');

        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>
</Relationships>');

        $zip->addFromString('word/document.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
    <w:body>
        <w:p>
            <w:r>
                <w:t>Test template {Number}</w:t>
            </w:r>
        </w:p>
    </w:body>
</w:document>');

        $zip->close();

        $content = file_get_contents($tmpFile);
        unlink($tmpFile);

        return base64_encode($content);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch list templates')]
    public function testBatchList(): void
    {
        $cnt = 0;
        foreach ($this->templateService->batch->list(1) as $item) {
            $cnt++;
        }

        self::assertGreaterThanOrEqual(0, $cnt);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch add templates')]
    public function testBatchAdd(): void
    {
        $items = [];
        $fileContent = $this->createMinimalDocxBase64();

        for ($i = 1; $i <= 3; $i++) {
            $items[] = [
                'name' => 'tpl-' . $this->faker->uuid(),
                'file' => $fileContent,
            ];
        }

        $ids = [];
        $cnt = 0;
        foreach ($this->templateService->batch->add($items) as $added) {
            $cnt++;
            $ids[] = $added->getId();
        }

        self::assertEquals(count($items), $cnt);

        $delCnt = 0;
        foreach ($this->templateService->batch->delete($ids) as $deleted) {
            $delCnt++;
        }

        self::assertEquals(count($items), $delCnt);
    }

    /**
     * @throws BaseException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch delete templates')]
    public function testBatchDelete(): void
    {
        $items = [];
        $fileContent = $this->createMinimalDocxBase64();

        for ($i = 1; $i <= 3; $i++) {
            $items[] = [
                'name' => 'tpl-' . $this->faker->uuid(),
                'file' => $fileContent,
            ];
        }

        $ids = [];
        foreach ($this->templateService->batch->add($items) as $added) {
            $ids[] = $added->getId();
        }

        $delCnt = 0;
        foreach ($this->templateService->batch->delete($ids) as $deleted) {
            $delCnt++;
        }

        self::assertEquals(count($items), $delCnt);
    }

    /**
     * @throws BaseException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch update templates')]
    public function testBatchUpdate(): void
    {
        $items = [];
        $fileContent = $this->createMinimalDocxBase64();

        for ($i = 1; $i <= 3; $i++) {
            $items[] = [
                'name' => 'tpl-' . $this->faker->uuid(),
                'file' => $fileContent,
            ];
        }

        $updatePayload = [];
        foreach ($this->templateService->batch->add($items) as $added) {
            $id = $added->getId();
            $updatePayload[$id] = [
                'fields' => [
                    'name' => 'updated-' . $id,
                ],
            ];
        }

        foreach ($this->templateService->batch->update($updatePayload) as $updated) {
            $this->assertTrue($updated->isSuccess());
        }

        // Cleanup
        $ids = array_keys($updatePayload);
        $deletedCount = 0;
        foreach ($this->templateService->batch->delete($ids) as $deleted) {
            $deletedCount++;
        }

        self::assertEquals(count($ids), $deletedCount);

        self::assertTrue(true);
    }
}

