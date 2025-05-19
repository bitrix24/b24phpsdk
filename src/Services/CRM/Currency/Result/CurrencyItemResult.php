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
use Bitrix24\SDK\Services\CRM\Common\Result\SystemFields\Types\Email;
use Bitrix24\SDK\Services\CRM\Common\Result\SystemFields\Types\InstantMessenger;
use Bitrix24\SDK\Services\CRM\Common\Result\SystemFields\Types\Phone;
use Bitrix24\SDK\Services\CRM\Common\Result\SystemFields\Types\Website;
use Carbon\CarbonImmutable;
use Money\Currency;
use Money\Money;

/**
 * Class CurrencyItemResult
 *
 * @property-read string $CURRENCY
 * @property-read string|null $HONORIFIC
 * @property-read string|null $BASE
 * @property-read int|null $AMOUNT_CNT
 * @property-read double|null $AMOUNT
 * @property-read int|null $SORT
 * @property-read array|null $LANG
 */
class CurrencyItemResult extends AbstractCrmItem
{
    /**
     * @param string $userfieldName
     *
     * @return mixed|null
     * @throws \Bitrix24\SDK\Services\CRM\Userfield\Exceptions\UserfieldNotFoundException
     */
    public function getUserfieldByFieldName(string $userfieldName)
    {
        return $this->getKeyWithUserfieldByFieldName($userfieldName);
    }
}
