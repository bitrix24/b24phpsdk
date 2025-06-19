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

namespace Bitrix24\SDK\Services\CRM\Quote\Result;

use Bitrix24\SDK\Services\CRM\Common\Result\AbstractCrmItem;
use Bitrix24\SDK\Services\CRM\Common\Result\SystemFields\Types\Email;
use Bitrix24\SDK\Services\CRM\Common\Result\SystemFields\Types\InstantMessenger;
use Bitrix24\SDK\Services\CRM\Common\Result\SystemFields\Types\Phone;
use Bitrix24\SDK\Services\CRM\Common\Result\SystemFields\Types\Website;
use Carbon\CarbonImmutable;
use Money\Currency;
use Money\Money;

/**
 * Class QuoteItemResult
 *
 * @property-read int $ID
 * @property-read string $TITLE
 * @property-read int|null $ASSIGNED_BY_ID
 * @property-read CarbonImmutable|null $BEGINDATA
 * @property-read CarbonImmutable|null $CLOSEDATA
 * @property-read string|null $CLIENT_ADDR
 * @property-read bool|null $CLOSED
 * @property-read string|null $COMMENTS
 * @property-read int|null $COMPANY_ID
 * @property-read int|null $CONTACT_ID
 * @property-read array|null $CONTACT_IDS
 * @property-read string|null $CONTENT
 * @property-read int|null $CREATED_BY_ID
 * @property-read Currency|null $CURRENCY_ID
 * @property-read CarbonImmutable|null $DATE_CREATE
 * @property-read CarbonImmutable|null $DATE_MODIFY
 * @property-read int|null $DEAL_ID
 * @property-read int|null $LEAD_ID
 * @property-read int|null $LOCATION_ID
 * @property-read bool|null $OPENED
 * @property-read Money|null $OPPORTUNITY
 * @property-read int|null $PERSON_TYPE_ID
 * @property-read string|null $QUOTE_NUMBER
 * @property-read string|null $STATUS_ID
 * @property-read string|null $TAX_VALUE
 * @property-read string|null $TERMS
 * @property-read string|null $UTM_SOURCE
 * @property-read string|null $UTM_MEDIUM
 * @property-read string|null $UTM_CAMPAIGN
 * @property-read string|null $UTM_CONTENT
 * @property-read string|null $UTM_TERM
 */
class QuoteItemResult extends AbstractCrmItem
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
