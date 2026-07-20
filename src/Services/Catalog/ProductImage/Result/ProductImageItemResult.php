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

namespace Bitrix24\SDK\Services\Catalog\ProductImage\Result;

use Bitrix24\SDK\Services\Catalog\Common\Result\AbstractCatalogItem;
use Carbon\CarbonImmutable;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read int $productId
 * @property-read string $type
 * @property-read CarbonImmutable|null $createTime
 * @property-read string|null $detailUrl
 * @property-read string|null $downloadUrl
 */
class ProductImageItemResult extends AbstractCatalogItem
{
}
