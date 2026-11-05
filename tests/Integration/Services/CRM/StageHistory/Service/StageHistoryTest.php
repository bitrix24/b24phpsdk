<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\StageHistory\Service;

use Bitrix24\SDK\Services\CRM\StageHistory\Service\StageHistory;
use Bitrix24\SDK\Tests\Integration\Services\CRM\StageHistory\StageHistoryFixture;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(StageHistory::class)]
class StageHistoryTest extends StageHistoryFixture
{
    public function testListFiltersHistoryForEachEntityKind(): void
    {
        foreach (self::$owners as $entityTypeId => $ownerId) {
            $result = self::$crm->stageHistory()->list($entityTypeId, ['ID' => 'ASC'], ['OWNER_ID' => $ownerId]);
            $items = $result->getStageHistoryItems();
            self::assertNotEmpty($items);
            foreach ($items as $item) {
                self::assertSame($ownerId, $item->OWNER_ID);
                self::assertInstanceOf(CarbonImmutable::class, $item->CREATED_TIME);
                self::assertNotEmpty($entityTypeId === 1 ? $item->STATUS_ID : $item->STAGE_ID);
            }

            self::assertGreaterThanOrEqual(count($items), $result->getTotal());
        }
    }

    public function testSelectionAndOffsetPagination(): void
    {
        foreach (self::$owners as $entityTypeId => $ownerId) {
            $first = self::$crm->stageHistory()->list($entityTypeId, ['ID' => 'ASC'], ['OWNER_ID' => $ownerId], ['ID', 'OWNER_ID']);
            $items = $first->getStageHistoryItems();
            self::assertNotEmpty($items);
            self::assertNull($items[0]->CREATED_TIME);
            $second = self::$crm->stageHistory()->list($entityTypeId, ['ID' => 'ASC'], ['OWNER_ID' => $ownerId], ['ID', 'OWNER_ID'], 50);
            self::assertSame([], $second->getStageHistoryItems());
            self::assertNull($second->getNextItem());
        }
    }

    public function testMissingOwnerHasEmptyResult(): void
    {
        self::assertSame([], self::$crm->stageHistory()->list(2, [], ['OWNER_ID' => -1])->getStageHistoryItems());
    }
}
