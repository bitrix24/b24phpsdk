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

namespace Bitrix24\SDK\Services\CRM\Lead\Result;

use Bitrix24\SDK\Services\CRM\Common\Result\AbstractCrmItem;
use Bitrix24\SDK\Services\CRM\Common\Result\SystemFields\Types\Email;
use Bitrix24\SDK\Services\CRM\Common\Result\SystemFields\Types\InstantMessenger;
use Bitrix24\SDK\Services\CRM\Common\Result\SystemFields\Types\Phone;
use Bitrix24\SDK\Services\CRM\Common\Result\SystemFields\Types\Website;
use Carbon\CarbonImmutable;
use Money\Currency;
use Money\Money;

/**
 * Class LeadItemResult
 *
 * @property-read int $ID
 * @property-read string $TITLE
 * @property-read string|null $HONORIFIC
 * @property-read string|null $NAME
 * @property-read string|null $SECOND_NAME
 * @property-read string|null $LAST_NAME
 * @property-read CarbonImmutable|null $BIRTHDATE
 * @property-read string|null $COMPANY_TITLE
 * @property-read string|null $SOURCE_ID
 * @property-read string|null $SOURCE_DESCRIPTION
 * @property-read string|null $STATUS_ID
 * @property-read string|null $STATUS_DESCRIPTION
 * @property-read string|null $STATUS_SEMANTIC_ID
 * @property-read string|null $POST
 * @property-read string|null $ADDRESS
 * @property-read string|null $ADDRESS_2
 * @property-read string|null $ADDRESS_CITY
 * @property-read string|null $ADDRESS_POSTAL_CODE
 * @property-read string|null $ADDRESS_REGION
 * @property-read string|null $ADDRESS_PROVINCE
 * @property-read string|null $ADDRESS_COUNTRY
 * @property-read string|null $ADDRESS_COUNTRY_CODE
 * @property-read int|null $ADDRESS_LOC_ADDR_ID
 * @property-read Currency|null $CURRENCY_ID
 * @property-read Money|null $OPPORTUNITY
 * @property-read bool|null $IS_MANUAL_OPPORTUNITY
 * @property-read bool|null $OPENED
 * @property-read string|null $COMMENTS
 * @property-read bool|null $HAS_PHONE
 * @property-read bool|null $HAS_EMAIL
 * @property-read bool|null $HAS_IMOL
 * @property-read int|null $ASSIGNED_BY_ID
 * @property-read int|null $CREATED_BY_ID
 * @property-read int|null $MODIFY_BY_ID
 * @property-read int|null $MOVED_BY_ID
 * @property-read CarbonImmutable|null $DATE_CREATE
 * @property-read CarbonImmutable|null $DATE_MODIFY
 * @property-read CarbonImmutable|null $MOVED_TIME
 * @property-read int|null $COMPANY_ID
 * @property-read int|null $CONTACT_ID
 * @property-read array|null $CONTACT_IDS
 * @property-read bool|null $IS_RETURN_CUSTOMER
 * @property-read CarbonImmutable|null $DATE_CLOSED
 * @property-read string|null $ORIGINATOR_ID
 * @property-read string|null $ORIGIN_ID
 * @property-read string|null $UTM_SOURCE
 * @property-read string|null $UTM_MEDIUM
 * @property-read string|null $UTM_CAMPAIGN
 * @property-read string|null $UTM_CONTENT
 * @property-read string|null $UTM_TERM
 * @property-read Phone[]|null $PHONE
 * @property-read Email[]|null $EMAIL
 * @property-read Website[]|null $WEB
 * @property-read InstantMessenger[]|null $IM
 * @property-read array|null $LINK
 * @property-read int|null $LAST_ACTIVITY_BY
 * @property-read CarbonImmutable|null $LAST_ACTIVITY_TIME
 */
class LeadItemResult extends AbstractCrmItem
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