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

namespace Bitrix24\SDK\Tests\Unit\Services\Main\UserHistoryChange\Result;

use Bitrix24\SDK\Services\Main\UserHistoryChange\Result\UserHistoryChangeItemResult;
use Bitrix24\SDK\Services\Main\UserHistoryChange\Result\UserHistoryChangesResult;
use Bitrix24\SDK\Tests\Unit\Services\Main\UserHistoryResponseFixture;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(UserHistoryChangeItemResult::class)]
#[CoversClass(UserHistoryChangesResult::class)]
class UserHistoryChangeResultTest extends TestCase
{
    use UserHistoryResponseFixture;

    #[DataProvider('payloads')]
    public function testPreservesData(mixed $data): void
    {
        $items = (new UserHistoryChangesResult(self::response(['items' => [[
            'id' => '10', 'historyId' => '42', 'field' => 'SYNTHETIC', 'data' => $data,
        ]]])))->getUserHistoryChanges();
        self::assertCount(1, $items);
        self::assertSame(10, $items[0]->id);
        self::assertSame(42, $items[0]->historyId);
        self::assertSame('SYNTHETIC', $items[0]->field);
        self::assertSame($data, $items[0]->data);
    }

    public static function payloads(): array
    {
        return [
            [['before' => 'old', 'after' => 'new']],
            [['before' => null, 'after' => ['ids' => [1, 2], 'active' => true]]],
            [null], [false], [42], ['raw'], [1.5],
        ];
    }

    public function testPartialSelection(): void
    {
        $userHistoryChangeItemResult = new UserHistoryChangeItemResult(['id' => 10]);
        self::assertNull($userHistoryChangeItemResult->historyId);
        self::assertNull($userHistoryChangeItemResult->field);
        self::assertNull($userHistoryChangeItemResult->data);
    }

    public function testEmptyList(): void
    {
        self::assertSame([], (new UserHistoryChangesResult(self::response(['items' => []])))->getUserHistoryChanges());
    }
}
