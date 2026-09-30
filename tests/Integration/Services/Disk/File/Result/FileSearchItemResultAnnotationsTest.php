<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Sally Fancen <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\Disk\File\Result;

use Bitrix24\SDK\Services\Disk\File\Result\FileSearchItemResult;
use Bitrix24\SDK\Services\Disk\File\Service\File;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Services\Disk\TemporaryDiskFixture;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(FileSearchItemResult::class)]
final class FileSearchItemResultAnnotationsTest extends TestCase
{
    use CustomBitrix24Assertions;

    public function testAllSystemFieldsAnnotated(): void
    {
        $fixture = TemporaryDiskFixture::create();
        try {
            $fields = [];
            foreach (['disk.file.getfields', 'disk.folder.getfields'] as $method) {
                $fields = array_merge($fields, $fixture->core->call($method)->getResponseData()->getResult());
            }

            // Search adds link fields; its item is the union of file and folder metadata.
            $result = $fixture->waitForSearch(new File($fixture->core, new NullLogger()));
            foreach ($result->getCoreResponse()->getResponseData()->getResult() as $rawItem) {
                $fields = array_merge($fields, $rawItem);
            }

            $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($fields), FileSearchItemResult::class);
        } finally {
            $fixture->delete();
        }
    }

    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $core = TemporaryDiskFixture::core();
        foreach (['disk.file.getfields', 'disk.folder.getfields'] as $method) {
            $fields = $core->call($method)->getResponseData()->getResult();
            $fields = array_map(static fn(array $field): array => array_change_key_case($field, CASE_LOWER), $fields);
            $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation($fields, FileSearchItemResult::class);
        }
    }

    public function testSearchFieldsAreAnnotatedAndMagicGettersCastBothObjectKinds(): void
    {
        $fixture = TemporaryDiskFixture::create();
        try {
            $result = $fixture->waitForSearch(new File($fixture->core, new NullLogger()));
            self::assertCount(2, $result->items());
            $fields = [];
            foreach ($result->getCoreResponse()->getResponseData()->getResult() as $rawItem) {
                $fields = array_merge($fields, $rawItem);
            }

            $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($fields), FileSearchItemResult::class);
            foreach ($result->items() as $item) {
                $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations($item, FileSearchItemResult::class);
            }
        } finally {
            $fixture->delete();
        }
    }
}
