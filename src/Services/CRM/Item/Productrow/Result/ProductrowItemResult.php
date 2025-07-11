<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Vadim Soluyanov <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\CRM\Item\Productrow\Result;

use Bitrix24\SDK\Services\CRM\Common\Result\AbstractCrmItem;
use Carbon\CarbonImmutable;
use Money\Currency;
use Money\Money;
use MoneyPHP\Percentage\Percentage;

/**
 * @property-read int $id
 * @property-read int $ownerId
 * @property-read string $ownerType
 * @property-read int $productId
 * @property-read string $productName
 * @property-read Money $price price with taxes and discounts
 * @property-read Money $priceExclusive without taxes but with discounts
 * @property-read Money $priceNetto  without taxes and discounts
 * @property-read Money $priceBrutto without discounts but with taxes
 * @property-read string $quantity
 * @property-read int $discountTypeId
 * @property-read Percentage $discountRate
 * @property-read Money $discountSum
 * @property-read Percentage $taxRate
 * @property-read bool $taxIncluded
 * @property-read bool $customized
 * @property-read int $measureCode
 * @property-read string $measureName
 * @property-read int $sort
 * @property-read int $type
 * @property-read int|null $storeId
 */
class ProductrowItemResult extends AbstractCrmItem
{
}
