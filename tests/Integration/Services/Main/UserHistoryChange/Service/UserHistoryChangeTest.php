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

namespace Bitrix24\SDK\Tests\Integration\Services\Main\UserHistoryChange\Service;

use Bitrix24\SDK\Core\Contracts\SortOrder;
use Bitrix24\SDK\Services\Main\UserHistoryChange\Service\UserHistoryChange;
use Bitrix24\SDK\Services\Main\UserHistoryChange\Service\UserHistoryChangeSelectBuilder;
use Bitrix24\SDK\Tests\Integration\Services\Main\UserHistory\HistoryFixture;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(UserHistoryChange::class)]
class UserHistoryChangeTest extends TestCase
{
    use HistoryFixture;

    public function testListReturnsChangesAssociatedWithHistory(): void
    {
        $item = $this->historyChangeItem();
        $result = $this->mainScope()->userHistoryChange()->list(
            (int)$item->historyId,
            (new UserHistoryChangeSelectBuilder())->allSystemFields(),
            [['id', '=', $item->id]],
            ['id' => SortOrder::Descending],
            ['limit' => 1]
        );
        $items = $result->getUserHistoryChanges();
        self::assertCount(1, $items);
        self::assertSame($item->historyId, $items[0]->historyId);
        self::assertGreaterThan(0, $items[0]->id);
        self::assertIsString($items[0]->field);
        $raw = $result->getCoreResponse()->getResponseData()->getResult()['items'][0];
        self::assertTrue($raw['data'] === $items[0]->data, 'Change data must be preserved without casting.');
    }

    public function testPartialSelectionOmitsUnselectedData(): void
    {
        $item = $this->historyChangeItem();
        $items = $this->mainScope()->userHistoryChange()->list((int)$item->historyId, ['id'], pagination: ['limit' => 1])
            ->getUserHistoryChanges();
        self::assertNotEmpty($items);
        self::assertNull($items[0]->data);
        self::assertNull($items[0]->field);
    }
}
