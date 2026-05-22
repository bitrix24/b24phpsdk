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

namespace Bitrix24\SDK\Services\Timeman\Result;

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;
use Carbon\CarbonImmutable;

/**
 * Represents a single workday item returned by timeman.open, timeman.pause, timeman.close, timeman.status methods.
 *
 * @property-read string $STATUS
 * @property-read CarbonImmutable|null $TIME_START
 * @property-read CarbonImmutable|null $TIME_FINISH
 * @property-read string $DURATION
 * @property-read string $TIME_LEAKS
 * @property-read bool $ACTIVE
 * @property-read string $IP_OPEN
 * @property-read string|null $IP_CLOSE
 * @property-read float $LAT_OPEN
 * @property-read float $LON_OPEN
 * @property-read float $LAT_CLOSE
 * @property-read float $LON_CLOSE
 * @property-read int $TZ_OFFSET
 */
class WorkdayItemResult extends AbstractAnnotatedItem
{
}

