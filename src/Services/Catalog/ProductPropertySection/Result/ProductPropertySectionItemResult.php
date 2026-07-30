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

namespace Bitrix24\SDK\Services\Catalog\ProductPropertySection\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;
use Bitrix24\SDK\Services\Catalog\ProductPropertySection\ProductPropertySectionDisplayType;

/**
 * @property-read int $propertyId
 * @property-read bool $smartFilter
 * @property-read ProductPropertySectionDisplayType $displayType
 * @property-read bool $displayExpanded
 * @property-read string $filterHint
 * @property-read int $iblockId
 * @property-read int $sectionId
 */
class ProductPropertySectionItemResult extends AbstractItem
{
    /**
     * @param int|string $offset
     *
     * @return bool|int|ProductPropertySectionDisplayType|string|null
     */
    public function __get($offset)
    {
        return match ($offset) {
            'smartFilter', 'displayExpanded' => $this->data[$offset] === 'Y',
            'displayType' => ProductPropertySectionDisplayType::from($this->data[$offset]),
            'propertyId', 'iblockId', 'sectionId' => (int)$this->data[$offset],
            'filterHint' => (string)$this->data[$offset],
            default => $this->data[$offset] ?? null,
        };
    }
}
