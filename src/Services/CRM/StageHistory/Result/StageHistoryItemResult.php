<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\CRM\StageHistory\Result;

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;
use Carbon\CarbonImmutable;

/**
 * @property-read int|null $ID
 * @property-read int|null $TYPE_ID
 * @property-read int|null $OWNER_ID
 * @property-read CarbonImmutable|null $CREATED_TIME
 * @property-read int|null $CATEGORY_ID
 * @property-read string|null $STAGE_ID
 * @property-read string|null $STAGE_SEMANTIC_ID
 * @property-read string|null $STATUS_ID
 * @property-read string|null $STATUS_SEMANTIC_ID
 */
class StageHistoryItemResult extends AbstractAnnotatedItem
{
}
