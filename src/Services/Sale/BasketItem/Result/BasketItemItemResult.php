<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Sally Fancen <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Sale\BasketItem\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;
use Carbon\CarbonImmutable;
use Money\Money;
use MoneyPHP\Percentage\Percentage;

/**
 * Class BasketItemItemResult
 * Represents a single basket item returned by Bitrix24 REST API.
 *
 * @property-read bool|null $barcodeMulti
 * @property-read Money|null $basePrice Original price excluding markups and discounts
 * @property-read bool|null $canBuy Availability flag (Y/N)
 * @property-read string|null $catalogXmlId External code of the product catalog
 * @property-read string|null $currency Currency code
 * @property-read bool|null $customPrice Price specified manually flag (Y/N)
 * @property-read CarbonImmutable|null $dateInsert
 * @property-read CarbonImmutable|null $dateUpdate
 * @property-read string|null $dimensions Dimensions of the product (serialized array)
 * @property-read Money|null $discountPrice Amount of the final discount or markup
 * @property-read int|null $id Basket item identifier
 * @property-read string|null $measureCode Code of the product's unit of measure
 * @property-read string|null $measureName Name of the unit of measure
 * @property-read string|null $name Name of the product
 * @property-read int|null $orderId Order identifier
 * @property-read Money|null $price Price including markups and discounts
 * @property-read int|null $productId Product identifier
 * @property-read string|null $productXmlId External code of the product
 * @property-read string|null $quantity Quantity of the product
 * @property-read int|null $sort Position in the list
 * @property-read int|null $type
 * @property-read bool|null $vatIncluded VAT included flag (Y/N)
 * @property-read Percentage|null $vatRate VAT rate in percentage
 * @property-read string|null $weight Weight of the product
 * @property-read string|null $xmlId External code of the basket item
 *
 * @package Bitrix24\SDK\Services\Sale\BasketItem\Result
 */
class BasketItemItemResult extends AbstractItem
{
}
