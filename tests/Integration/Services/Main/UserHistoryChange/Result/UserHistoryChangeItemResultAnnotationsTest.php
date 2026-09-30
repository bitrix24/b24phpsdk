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

namespace Bitrix24\SDK\Tests\Integration\Services\Main\UserHistoryChange\Result;

use Bitrix24\SDK\Services\Main\UserHistoryChange\Result\UserHistoryChangeItemResult;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Services\Main\UserHistory\HistoryFixture;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(UserHistoryChangeItemResult::class)]
class UserHistoryChangeItemResultAnnotationsTest extends TestCase
{
    use CustomBitrix24Assertions;
    use HistoryFixture;

    public function testAllSystemFieldsAnnotated(): void
    {
        $metadata = $this->mainScope()->userHistoryChange()->fields()->getFieldsDescription();
        self::assertNotEmpty($metadata);
        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($metadata), UserHistoryChangeItemResult::class);
    }

    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $metadata = $this->mainScope()->userHistoryChange()->fields()->getFieldsDescription();
        self::assertSame('object', $metadata['data']['type']);
        // Live metadata is less precise than OpenAPI: date-time / unconstrained JSON.
        // Normalize only this known field for the shared assertion; keep API results raw.
        $metadata['data']['type'] = 'any';
        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation($metadata, UserHistoryChangeItemResult::class);
    }
}
