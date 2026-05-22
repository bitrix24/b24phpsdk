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

use Bitrix24\SDK\Core\Result\AbstractItem;
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
class WorkdayItemResult extends AbstractItem
{
    public function __get($offset)
    {
        return match ($offset) {
            'TIME_START', 'TIME_FINISH' => isset($this->data[$offset])
                ? CarbonImmutable::createFromFormat(DATE_ATOM, $this->data[$offset])
                : null,
            'TZ_OFFSET' => isset($this->data[$offset]) ? (int)$this->data[$offset] : null,
            'LAT_OPEN', 'LON_OPEN', 'LAT_CLOSE', 'LON_CLOSE' => isset($this->data[$offset]) ? (float)$this->data[$offset] : null,
            default => $this->data[$offset] ?? null,
        };
    }
}
