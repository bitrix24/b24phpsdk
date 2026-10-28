<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\Note\File\Result;

use Bitrix24\SDK\Services\Note\File\Result\FileItemResult;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FileItemResult::class)]
class FileItemResultAnnotationsTest extends TestCase
{
    use CustomBitrix24Assertions;

    public function testAllSystemFieldsAnnotated(): void
    {
        $fields = Factory::getServiceBuilder()->getNoteScope()->fileField()->list()->getFieldsDescription();
        self::assertNotEmpty($fields);
        self::assertArrayHasKey('createdAt', $fields);
        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($fields), FileItemResult::class);
    }

    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $fields = Factory::getServiceBuilder()->getNoteScope()->fileField()->list()->getFieldsDescription();
        self::assertNotEmpty($fields);
        // Live REST v3 reports object; OpenAPI and list responses specify an ISO 8601 date-time.
        self::assertContains($fields['createdAt']['type'], ['object', 'string', 'datetime']);
        $fields['createdAt']['type'] = 'datetime';
        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation($fields, FileItemResult::class);
    }
}
