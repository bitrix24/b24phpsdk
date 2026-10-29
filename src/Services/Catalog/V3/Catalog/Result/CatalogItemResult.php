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

namespace Bitrix24\SDK\Services\Catalog\V3\Catalog\Result;

use Bitrix24\SDK\Attributes\OpenApiEntity;
use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;
use Bitrix24\SDK\Services\Catalog\V3\Catalog\Service\CatalogSelectBuilder;

/**
 * @property-read int         $id               equals iblockId
 * @property-read int|null    $iblockId
 * @property-read bool|null   $yandexExport     REST v3 returns Y/N
 * @property-read bool|null   $subscription     REST v3 returns Y/N
 * @property-read int|null    $vatId            0 when no VAT rate is set
 * @property-read int|null    $productIblockId  0 for a product catalog, parent iblock for an offers catalog
 * @property-read int|null    $skuPropertyId
 * @property-read string|null $iblockTypeId
 * @property-read string|null $lid
 * @property-read string|null $name
 */
#[OpenApiEntity(
    entityKey:     'bitrix.catalog.catalogdto',
    selectBuilder: CatalogSelectBuilder::class,
)]
class CatalogItemResult extends AbstractAnnotatedItem
{
}
