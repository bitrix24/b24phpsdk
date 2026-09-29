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

namespace Bitrix24\SDK\Services\Catalog\Product\Sku\Result;

use Bitrix24\SDK\Services\Catalog\Common\ProductType;
use Bitrix24\SDK\Services\Catalog\Common\Result\AbstractCatalogItem;
use Carbon\CarbonImmutable;

/**
 * @property-read bool $active
 * @property-read bool $available
 * @property-read bool $bundle
 * @property-read ?bool $canBuyZero
 * @property-read string $code
 * @property-read int $createdBy
 * @property-read CarbonImmutable|null $dateActiveFrom
 * @property-read CarbonImmutable|null $dateActiveTo
 * @property-read CarbonImmutable $dateCreate
 * @property-read array|null $detailPicture
 * @property-read string $detailText
 * @property-read string $detailTextType
 * @property-read ?string $height
 * @property-read int $id
 * @property-read int $iblockId
 * @property-read ?array $iblockSection
 * @property-read ?int $iblockSectionId
 * @property-read ?string $length
 * @property-read ?int $measure
 * @property-read int $modifiedBy
 * @property-read string $name
 * @property-read array|null $previewPicture
 * @property-read string $previewText
 * @property-read string $previewTextType
 * @property-read ?string $purchasingCurrency
 * @property-read ?string $purchasingPrice
 * @property-read ?string $quantity
 * @property-read int $sort
 * @property-read bool $subscribe
 * @property-read CarbonImmutable $timestampX
 * @property-read ProductType $type
 * @property-read ?int $vatId
 * @property-read bool $vatIncluded
 * @property-read ?string $weight
 * @property-read ?string $width
 * @property-read string $xmlId
 */
class SkuItemResult extends AbstractCatalogItem
{
}
