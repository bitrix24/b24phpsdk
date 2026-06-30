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

namespace Bitrix24\SDK\Services\Timeman\Record\Result;

use Bitrix24\SDK\Attributes\OpenApiEntity;
use Bitrix24\SDK\Core\Result\AbstractItem;
use Bitrix24\SDK\Services\Timeman\Record\Service\RecordSelectBuilder;
use Carbon\CarbonImmutable;

/**
 * Single work-time record returned by `timeman.record.list`.
 *
 * Note: the `state` object defined on the `bitrix.timeman.recorddto` entity is exposed only
 * through the `timeman.record.field.*` metadata and is never returned inside list items,
 * so it is intentionally not annotated here.
 *
 * @property-read int                  $id
 * @property-read int|null             $userId
 * @property-read CarbonImmutable|null $startTime
 * @property-read CarbonImmutable|null $endTime
 * @property-read int|null             $duration
 * @property-read int|null             $breakLength
 * @property-read bool|null            $isApproved
 */
#[OpenApiEntity(
    entityKey:     'bitrix.timeman.recorddto',
    selectBuilder: RecordSelectBuilder::class,
)]
class RecordItemResult extends AbstractItem
{
    /**
     * @param int|string $offset
     *
     * @return int|CarbonImmutable|mixed|null
     */
    #[\Override]
    public function __get($offset)
    {
        return match ($offset) {
            'id' => (int)$this->data[$offset],
            'userId', 'duration', 'breakLength' => ($this->data[$offset] !== null && $this->data[$offset] !== '')
                ? (int)$this->data[$offset]
                : null,
            'startTime', 'endTime' => ($this->data[$offset] !== null && $this->data[$offset] !== '')
                ? CarbonImmutable::createFromFormat(DATE_ATOM, $this->data[$offset])
                : null,
            default => $this->data[$offset] ?? null,
        };
    }
}
