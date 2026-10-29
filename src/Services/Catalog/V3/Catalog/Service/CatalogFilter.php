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

namespace Bitrix24\SDK\Services\Catalog\V3\Catalog\Service;

use Bitrix24\SDK\Filters\AbstractFilterBuilder;
use Bitrix24\SDK\Filters\Types\BoolFieldConditionBuilder;
use Bitrix24\SDK\Filters\Types\IntFieldConditionBuilder;
use Bitrix24\SDK\Filters\Types\StringFieldConditionBuilder;

/**
 * Typed filter for `catalog.catalog.list`.
 */
class CatalogFilter extends AbstractFilterBuilder
{
    public function id(): IntFieldConditionBuilder
    {
        return new IntFieldConditionBuilder('id', $this);
    }

    public function iblockId(): IntFieldConditionBuilder
    {
        return new IntFieldConditionBuilder('iblockId', $this);
    }

    public function vatId(): IntFieldConditionBuilder
    {
        return new IntFieldConditionBuilder('vatId', $this);
    }

    public function productIblockId(): IntFieldConditionBuilder
    {
        return new IntFieldConditionBuilder('productIblockId', $this);
    }

    public function skuPropertyId(): IntFieldConditionBuilder
    {
        return new IntFieldConditionBuilder('skuPropertyId', $this);
    }

    public function iblockTypeId(): StringFieldConditionBuilder
    {
        return new StringFieldConditionBuilder('iblockTypeId', $this);
    }

    public function lid(): StringFieldConditionBuilder
    {
        return new StringFieldConditionBuilder('lid', $this);
    }

    public function name(): StringFieldConditionBuilder
    {
        return new StringFieldConditionBuilder('name', $this);
    }

    // catalogs filter their flags by the Y/N strings this builder emits
    public function yandexExport(): BoolFieldConditionBuilder
    {
        return new BoolFieldConditionBuilder('yandexExport', $this);
    }

    public function subscription(): BoolFieldConditionBuilder
    {
        return new BoolFieldConditionBuilder('subscription', $this);
    }
}
