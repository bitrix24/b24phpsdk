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

namespace Bitrix24\SDK\Services\Main\Result;

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;
use Carbon\CarbonImmutable;

/**
 * Offline event item returned by event.offline.get / event.offline.list.
 *
 * EVENT_DATA / EVENT_ADDITIONAL are annotated as array: the API returns `false` when empty and
 * an array otherwise; AbstractAnnotatedItem coerces the value to an array on access.
 *
 * @property-read int                  $ID
 * @property-read CarbonImmutable|null $TIMESTAMP_X
 * @property-read string|null          $EVENT_NAME
 * @property-read array                $EVENT_DATA
 * @property-read array                $EVENT_ADDITIONAL
 * @property-read string               $MESSAGE_ID
 * @property-read string|null          $PROCESS_ID
 * @property-read int                  $ERROR
 */
class OfflineEventItemResult extends AbstractAnnotatedItem
{
}
