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

namespace Bitrix24\SDK\Services\CRM\Currency\Result;

use Bitrix24\SDK\Services\CRM\Common\Result\AbstractCrmItem;
use Carbon\CarbonImmutable;

/**
 * Class CurrencyItemResult
 *
 * @property-read string $CURRENCY
 * @property-read int|null $AMOUNT_CNT
 * @property-read Money\Money|null $AMOUNT
 * @property-read int|null $SORT
 * @property-read bool|null $BASE
 * @property-read string|null $FULL_NAME
 * @property-read string|null $LID
 * @property-read string|null $FORMAT_STRING
 * @property-read string|null $DEC_POINT
 * @property-read string|null $THOUSANDS_SEP
 * @property-read int|null $DECIMALS
 * @property-read CarbonImmutable|null $DATE_UPDATE
 * @property-read array|null $LANG
 */
class CurrencyItemResult extends AbstractCrmItem
{
    
}
