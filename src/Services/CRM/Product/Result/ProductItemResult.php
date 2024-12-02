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

namespace Bitrix24\SDK\Services\CRM\Product\Result;

use Bitrix24\SDK\Services\CRM\Common\Result\AbstractCrmItem;
use Carbon\CarbonImmutable;
use Money\Currency;
use Money\Money;

/**
 * Class ProductItemResult
 *
 * @property-read int $ID
 * @property-read int $CATALOG_ID
 * @property-read Money $PRICE
 * @property-read Currency $CURRENCY_ID
 * @property-read string $NAME
 * @property-read string $CODE
 * @property-read string $DESCRIPTION
 * @property-read string $DESCRIPTION_TYPE
 * @property-read bool $ACTIVE
 * @property-read int $SECTION_ID
 * @property-read int $SORT
 * @property-read int $VAT_ID
 * @property-read bool $VAT_INCLUDED
 * @property-read int $MEASURE
 * @property-read string $XML_ID
 * @property-read array $PREVIEW_PICTURE
 * @property-read array $DETAIL_PICTURE
 * @property-read CarbonImmutable $DATE_CREATE
 * @property-read CarbonImmutable $TIMESTAMP_X
 * @property-read int $MODIFIED_BY
 * @property-read int $CREATED_BY
 */
class ProductItemResult extends AbstractCrmItem
{
}