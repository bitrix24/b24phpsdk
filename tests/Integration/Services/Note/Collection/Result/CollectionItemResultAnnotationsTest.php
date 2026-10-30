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

namespace Bitrix24\SDK\Tests\Integration\Services\Note\Collection\Result;

use Bitrix24\SDK\Services\Note\Collection\Result\CollectionItemResult;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CollectionItemResult::class)]
class CollectionItemResultAnnotationsTest extends TestCase
{
    use CustomBitrix24Assertions;

    private const DATE_TIME_FIELDS = ['createdAt', 'updatedAt'];

    public function testAllSystemFieldsAnnotated(): void
    {
        $fields = Factory::getServiceBuilder()->getNoteScope()->collection()->fieldList()->getFieldsDescription();
        self::assertNotEmpty($fields);
        self::assertArrayHasKey('state', $fields);
        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($fields), CollectionItemResult::class);
    }

    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $fields = Factory::getServiceBuilder()->getNoteScope()->collection()->fieldList()->getFieldsDescription();
        self::assertNotEmpty($fields);
        // Live REST v3 reports object; OpenAPI and item responses specify an ISO 8601 date-time.
        foreach (self::DATE_TIME_FIELDS as $name) {
            self::assertContains($fields[$name]['type'], ['object', 'string', 'datetime']);
            $fields[$name]['type'] = 'datetime';
        }

        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation($fields, CollectionItemResult::class);
    }
}
