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

namespace Bitrix24\SDK\Tests\Integration\Services\Main\UserHistory\Service;

use Bitrix24\SDK\Core\Contracts\SortOrder;
use Bitrix24\SDK\Services\Main\UserHistory\Service\UserHistory;
use Bitrix24\SDK\Services\Main\UserHistory\Service\UserHistoryTailCursor;
use Bitrix24\SDK\Tests\Integration\Services\Main\UserHistory\HistoryFixture;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(UserHistory::class)]
class UserHistoryTest extends TestCase
{
    use HistoryFixture;

    public function testListReturnsTypedHistoryWithExplicitField(): void
    {
        $item = $this->historyItem();
        self::assertGreaterThan(0, $item->id);
        self::assertSame($this->userId(), $item->userId);
        self::assertIsInt($item->eventType);
        self::assertInstanceOf(CarbonImmutable::class, $item->dateInsert);
        self::assertIsString($item->field);
    }

    public function testPartialSelectionAndAdditionalFilter(): void
    {
        $id = $this->historyItem()->id;
        $items = $this->mainScope()->userHistory()->list(
            $this->userId(),
            ['id'],
            [['id', '=', $id]],
            ['id' => SortOrder::Descending],
            ['limit' => 1]
        )->getUserHistoryItems();
        self::assertCount(1, $items);
        self::assertSame($id, $items[0]->id);
        self::assertNull($items[0]->dateInsert);
        self::assertNull($items[0]->field);
    }

    public function testTailProgressionUsesReturnedCursor(): void
    {
        $this->historyItem();
        $first = $this->mainScope()->userHistory()->tail($this->userId(), new UserHistoryTailCursor(0), ['id']);
        self::assertNotEmpty($first->getUserHistoryItems());
        $cursor = $first->getCursor();
        self::assertNotNull($cursor);
        self::assertNotNull($cursor['value']);
        $next = $this->mainScope()->userHistory()->tail(
            $this->userId(),
            new UserHistoryTailCursor($cursor['value'], $cursor['field']),
            ['id']
        );
        foreach ($next->getUserHistoryItems() as $item) {
            self::assertGreaterThan((int)$cursor['value'], $item->id);
        }

        if ($next->getUserHistoryItems() === []) {
            self::assertFalse($next->hasMore());
            self::assertNull($next->getCursor()['value']);
        }
    }

    public function testTailAfterNewestEntryPreservesEmptyCursor(): void
    {
        $latest = (int)$this->historyItem()->id;
        $result = $this->mainScope()->userHistory()->tail($this->userId(), new UserHistoryTailCursor($latest), ['id']);
        foreach ($result->getUserHistoryItems() as $item) {
            // Concurrent new entries are valid; every item must advance the checkpoint.
            self::assertGreaterThan($latest, $item->id);
        }

        if ($result->getUserHistoryItems() === []) {
            self::assertFalse($result->hasMore());
            self::assertSame(['field' => 'id', 'value' => null], $result->getCursor());
        }
    }
}
