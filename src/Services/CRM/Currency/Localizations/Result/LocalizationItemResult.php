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

namespace Bitrix24\SDK\Services\CRM\Currency\Localizations\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * Class LocalizationItemResult
 *
 * @property-read string|null $FORMAT_STRING
 * @property-read string|null $FULL_NAME
 * @property-read string|null $DEC_POINT
 * @property-read string|null $THOUSANDS_SEP
 * @property-read int|null $DECIMALS
 * @property-read string|null $THOUSANDS_VARIANT
 * @property-read bool|null $HIDE_ZERO
 */
class LocalizationItemResult extends AbstractItem
{
    
}
