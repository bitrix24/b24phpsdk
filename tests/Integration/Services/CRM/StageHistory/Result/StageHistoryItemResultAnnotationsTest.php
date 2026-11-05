<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\StageHistory\Result;

use Bitrix24\SDK\Services\CRM\StageHistory\Result\StageHistoryItemResult;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Services\CRM\StageHistory\StageHistoryFixture;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(StageHistoryItemResult::class)]
class StageHistoryItemResultAnnotationsTest extends StageHistoryFixture
{
    use CustomBitrix24Assertions;

    public function testAllSystemFieldsAnnotated(): void
    {
        // crm.stagehistory has no fields method; compare with live full records.
        $fieldNames = [];
        foreach (self::$owners as $entityTypeId => $ownerId) {
            $raw = self::$crm->stageHistory()->list($entityTypeId, [], ['OWNER_ID' => $ownerId])->getCoreResponse()->getResponseData()->getResult()['items'];
            self::assertNotEmpty($raw);
            foreach ($raw as $item) {
                $fieldNames = array_merge($fieldNames, array_keys($item));
            }
        }

        $this->assertBitrix24AllResultItemFieldsAnnotated(array_values(array_unique($fieldNames)), StageHistoryItemResult::class);
    }

    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        foreach (self::$owners as $entityTypeId => $ownerId) {
            $items = self::$crm->stageHistory()->list($entityTypeId, [], ['OWNER_ID' => $ownerId])->getStageHistoryItems();
            self::assertNotEmpty($items);
            foreach ($items as $item) {
                $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations($item, StageHistoryItemResult::class);
            }
        }
    }
}
