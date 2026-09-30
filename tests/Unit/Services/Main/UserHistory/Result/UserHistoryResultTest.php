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

namespace Bitrix24\SDK\Tests\Unit\Services\Main\UserHistory\Result;

use Bitrix24\SDK\Services\Main\UserHistory\Result\UserHistoryItemResult;
use Bitrix24\SDK\Services\Main\UserHistory\Result\UserHistoriesResult;
use Bitrix24\SDK\Services\Main\UserHistory\Result\UserHistoryTailResult;
use Bitrix24\SDK\Tests\Unit\Services\Main\UserHistoryResponseFixture;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(UserHistoryItemResult::class)]
#[CoversClass(UserHistoriesResult::class)]
#[CoversClass(UserHistoryTailResult::class)]
class UserHistoryResultTest extends TestCase
{
    use UserHistoryResponseFixture;

    public function testTypedHistoryAndOmittedField(): void
    {
        $userHistoriesResult = new UserHistoriesResult(self::response(['items' => [[
            'id' => '42', 'userId' => '7', 'eventType' => '2', 'updatedById' => '8',
            'dateInsert' => '2026-09-30T08:15:00+06:00',
            'remoteAddr' => '192.0.2.1', 'userAgent' => 'Synthetic client', 'requestUri' => '/synthetic',
        ]]]));
        $items = $userHistoriesResult->getUserHistoryItems();
        self::assertCount(1, $items);
        $item = $items[0];
        self::assertSame(42, $item->id);
        self::assertSame(7, $item->userId);
        self::assertSame(2, $item->eventType);
        self::assertSame(8, $item->updatedById);
        self::assertInstanceOf(CarbonImmutable::class, $item->dateInsert);
        self::assertSame('2026-09-30T08:15:00+06:00', $item->dateInsert->toIso8601String());
        self::assertNull($item->field);
        self::assertSame('192.0.2.1', $item->remoteAddr);
    }

    public function testPartialSelectionAndExplicitField(): void
    {
        $userHistoryItemResult = new UserHistoryItemResult(['field' => 'NAME']);
        self::assertSame('NAME', $userHistoryItemResult->field);
        self::assertNull($userHistoryItemResult->id);
        self::assertNull($userHistoryItemResult->dateInsert);
        self::assertNull($userHistoryItemResult->userId);
    }

    public function testEmptyList(): void
    {
        self::assertSame([], (new UserHistoriesResult(self::response(['items' => []])))->getUserHistoryItems());
    }

    #[DataProvider('cursorValues')]
    public function testTailPreservesCursor(int|string|null $value, bool $hasMore): void
    {
        $cursor = ['field' => 'id', 'value' => $value];
        $userHistoryTailResult = new UserHistoryTailResult(self::response(['items' => [], 'cursor' => $cursor, 'hasMore' => $hasMore]));
        self::assertSame([], $userHistoryTailResult->getUserHistoryItems());
        self::assertSame($cursor, $userHistoryTailResult->getCursor());
        self::assertSame($hasMore, $userHistoryTailResult->hasMore());
    }

    public static function cursorValues(): array
    {
        return [['42', true], [42, false], [null, false]];
    }

    public function testAbsentCursorDoesNotBecomeZero(): void
    {
        $userHistoryTailResult = new UserHistoryTailResult(self::response(['items' => [], 'hasMore' => false]));
        self::assertNull($userHistoryTailResult->getCursor());
        self::assertFalse($userHistoryTailResult->hasMore());
    }
}
