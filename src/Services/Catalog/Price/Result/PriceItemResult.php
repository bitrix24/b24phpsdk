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

namespace Bitrix24\SDK\Services\Catalog\Price\Result;

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;
use Carbon\CarbonImmutable;

/**
 * @property-read int                  $id
 * @property-read int                  $productId
 * @property-read int                  $catalogGroupId
 * @property-read float                $price
 * @property-read string               $currency
 * @property-read int|null             $extraId
 * @property-read int|null             $quantityFrom
 * @property-read int|null             $quantityTo
 * @property-read float|null           $priceScale
 * @property-read CarbonImmutable|null $timestampX
 */
class PriceItemResult extends AbstractAnnotatedItem
{
}
