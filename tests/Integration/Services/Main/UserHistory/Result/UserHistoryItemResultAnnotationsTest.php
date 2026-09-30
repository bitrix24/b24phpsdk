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

namespace Bitrix24\SDK\Tests\Integration\Services\Main\UserHistory\Result;

use Bitrix24\SDK\Services\Main\UserHistory\Result\UserHistoryItemResult;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Services\Main\UserHistory\HistoryFixture;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(UserHistoryItemResult::class)]
class UserHistoryItemResultAnnotationsTest extends TestCase
{
    use CustomBitrix24Assertions;
    use HistoryFixture;

    public function testAllSystemFieldsAnnotated(): void
    {
        $metadata = $this->mainScope()->userHistory()->fields()->getFieldsDescription();
        self::assertNotEmpty($metadata);
        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($metadata), UserHistoryItemResult::class);
    }

    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $metadata = $this->mainScope()->userHistory()->fields()->getFieldsDescription();
        self::assertSame('object', $metadata['dateInsert']['type']);
        // Live metadata is less precise than OpenAPI: date-time / unconstrained JSON.
        // Normalize only this known field for the shared assertion; keep API results raw.
        $metadata['dateInsert']['type'] = 'datetime';
        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation($metadata, UserHistoryItemResult::class);
    }
}
