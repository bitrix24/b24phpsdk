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

namespace Bitrix24\SDK\Services\Catalog\PriceType\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;
use Carbon\CarbonImmutable;

/**
 * @property-read int                  $id
 * @property-read string               $name
 * @property-read bool                 $base
 * @property-read int                  $sort
 * @property-read string|null          $xmlId
 * @property-read int|null             $createdBy
 * @property-read int|null             $modifiedBy
 * @property-read CarbonImmutable|null $dateCreate
 * @property-read CarbonImmutable|null $timestampX
 */
class PriceTypeItemResult extends AbstractItem
{
    /**
     * @param int|string $offset
     *
     * @return bool|CarbonImmutable|mixed|null
     */
    public function __get($offset)
    {
        return match ($offset) {
            'base' => $this->data[$offset] === 'Y',
            'dateCreate', 'timestampX' => $this->data[$offset] !== null && $this->data[$offset] !== ''
                ? CarbonImmutable::createFromFormat(DATE_ATOM, $this->data[$offset])
                : null,
            default => $this->data[$offset] ?? null,
        };
    }
}
