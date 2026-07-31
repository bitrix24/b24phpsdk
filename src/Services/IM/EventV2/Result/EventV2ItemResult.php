<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <algonexys@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\IM\EventV2\Result;

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;
use Carbon\CarbonImmutable;

/**
 * A single event item returned by im.v2.Event.get.
 *
 * @property-read int $eventId      Event ID; pass as `offset` in the next call to acknowledge
 * @property-read string $type      Event type (e.g. ONIMV2MESSAGEADD)
 * @property-read ?CarbonImmutable $date  Date and time of the event
 * @property-read array<string, mixed> $data  Event payload; structure depends on the event type
 */
class EventV2ItemResult extends AbstractAnnotatedItem
{
}
