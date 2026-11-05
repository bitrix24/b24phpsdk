<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Services\CRM\StageHistory\Service;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Services\CRM\CRMServiceBuilder;
use Bitrix24\SDK\Services\CRM\StageHistory\Result\StageHistoryItemResult;
use Bitrix24\SDK\Services\CRM\StageHistory\Result\StageHistoryResult;
use Bitrix24\SDK\Services\CRM\StageHistory\Service\StageHistory;
use Bitrix24\SDK\Services\ServiceBuilder;
use Bitrix24\SDK\Tests\Unit\Services\CRM\CallList\ResponseFixture;
use Bitrix24\SDK\Tests\Unit\Stubs\NullBatch;
use Bitrix24\SDK\Tests\Unit\Stubs\NullBulkItemsReader;
use Bitrix24\SDK\Tests\Unit\Stubs\NullCore;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(StageHistory::class)]
#[CoversClass(StageHistoryResult::class)]
#[CoversClass(StageHistoryItemResult::class)]
#[CoversClass(CRMServiceBuilder::class)]
class StageHistoryTest extends TestCase
{
    use ResponseFixture;

    public function testListMapsParametersAndReadsNestedItemsWithPagination(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $core->expects(self::once())->method('call')->with('crm.stagehistory.list', [
            'entityTypeId' => 1038, 'order' => ['ID' => 'ASC'], 'filter' => ['OWNER_ID' => 19],
            'select' => ['ID', 'OWNER_ID', 'CREATED_TIME', 'STAGE_ID'], 'start' => 50,
        ])->willReturn($this->responseBody(['result' => ['items' => [[
            'ID' => '51', 'OWNER_ID' => '19', 'CREATED_TIME' => '2026-11-01T14:41:44+03:00', 'STAGE_ID' => 'DT1038_15:FAIL',
        ]]], 'next' => 100, 'total' => 120], 'crm.stagehistory.list'));
        $result = (new StageHistory($core, new NullLogger()))->list(1038, ['ID' => 'ASC'], ['OWNER_ID' => 19], ['ID', 'OWNER_ID', 'CREATED_TIME', 'STAGE_ID'], 50);
        self::assertSame(100, $result->getNextItem());
        self::assertSame(120, $result->getTotal());
        $items = $result->getStageHistoryItems();
        self::assertCount(1, $items);
        self::assertSame(51, $items[0]->ID);
        self::assertSame(19, $items[0]->OWNER_ID);
        self::assertInstanceOf(CarbonImmutable::class, $items[0]->CREATED_TIME);
        self::assertSame('DT1038_15:FAIL', $items[0]->STAGE_ID);
        self::assertNull($items[0]->STATUS_ID);
        self::assertNull($items[0]->CATEGORY_ID);
    }

    public function testListDefaultsAndEmptyLastPage(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $core->expects(self::once())->method('call')->with('crm.stagehistory.list', [
            'entityTypeId' => 1, 'order' => [], 'filter' => [], 'select' => [], 'start' => 0,
        ])->willReturn($this->responseBody(['result' => ['items' => []], 'total' => 0], 'crm.stagehistory.list'));
        $result = (new StageHistory($core, new NullLogger()))->list(1);
        self::assertSame([], $result->getStageHistoryItems());
        self::assertNull($result->getNextItem());
        self::assertSame(0, $result->getTotal());
    }

    public function testLeadStatusFieldsAndPartialSelectedRecord(): void
    {
        $item = new StageHistoryItemResult(['ID' => '3', 'STATUS_ID' => 'CONVERTED', 'STATUS_SEMANTIC_ID' => 'S', 'TYPE_ID' => '3']);
        self::assertSame(3, $item->ID);
        self::assertSame(3, $item->TYPE_ID);
        self::assertSame('CONVERTED', $item->STATUS_ID);
        self::assertSame('S', $item->STATUS_SEMANTIC_ID);
        self::assertNull($item->STAGE_ID);
        self::assertNull($item->CREATED_TIME);
    }

    public function testBuilderCachesStageHistoryService(): void
    {
        $builder = new ServiceBuilder(new NullCore(), new NullBatch(), new NullBulkItemsReader(), new NullLogger());
        self::assertInstanceOf(StageHistory::class, $builder->getCRMScope()->stageHistory());
        self::assertSame($builder->getCRMScope()->stageHistory(), $builder->getCRMScope()->stageHistory());
    }
}
