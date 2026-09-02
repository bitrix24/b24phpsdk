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

namespace Bitrix24\SDK\Services\Catalog\Vat\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;
use Carbon\CarbonImmutable;

/**
 * @property-read int                  $id
 * @property-read string               $name
 * @property-read bool                 $active
 * @property-read float                $rate
 * @property-read int                  $sort
 * @property-read CarbonImmutable|null $timestampX
 */
class VatItemResult extends AbstractItem
{
    /**
     * @param int|string $offset
     *
     * @return bool|CarbonImmutable|float|mixed|null
     */
    public function __get($offset)
    {
        return match ($offset) {
            'active' => $this->data[$offset] === 'Y',
            'rate' => (float)$this->data[$offset],
            'timestampX' => $this->data[$offset] !== null && $this->data[$offset] !== ''
                ? CarbonImmutable::createFromFormat(DATE_ATOM, $this->data[$offset])
                : null,
            default => $this->data[$offset] ?? null,
        };
    }
}
