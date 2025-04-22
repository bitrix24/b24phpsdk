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

namespace Bitrix24\SDK\Services\CRM\VatRates\Result;

use Bitrix24\SDK\Services\CRM\Common\Result\AbstractCrmItem;
use Bitrix24\SDK\Services\CRM\Common\Result\SystemFields\Types\Email;
use Bitrix24\SDK\Services\CRM\Common\Result\SystemFields\Types\InstantMessenger;
use Bitrix24\SDK\Services\CRM\Common\Result\SystemFields\Types\Phone;
use Bitrix24\SDK\Services\CRM\Common\Result\SystemFields\Types\Website;
use Carbon\CarbonImmutable;
use Money\Currency;
use Money\Money;
use MoneyPHP\Percentage\Percentage;

/**
 * Class LeadItemResult
 *
 * @property-read int $ID
 * @property-read CarbonImmutable|null $TIMESTAMP_X
 * @property-read bool|null $ACTIVE
 * @property-read int|null $C_SORT
 * @property-read string|null $NAME
 * @property-read Percentage|null $RATE
 */
class VatRateItemResult extends AbstractCrmItem
{
}
