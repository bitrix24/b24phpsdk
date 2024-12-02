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

namespace Bitrix24\SDK\Services\CRM\Company\Result;

use Bitrix24\SDK\Services\CRM\Common\Result\AbstractCrmItem;
use Bitrix24\SDK\Services\CRM\Common\Result\SystemFields\Types\Email;
use Bitrix24\SDK\Services\CRM\Common\Result\SystemFields\Types\File;
use Bitrix24\SDK\Services\CRM\Common\Result\SystemFields\Types\InstantMessenger;
use Bitrix24\SDK\Services\CRM\Common\Result\SystemFields\Types\Phone;
use Bitrix24\SDK\Services\CRM\Common\Result\SystemFields\Types\Website;
use Bitrix24\SDK\Services\CRM\Userfield\Exceptions\UserfieldNotFoundException;
use Carbon\CarbonImmutable;
use Money\Currency;
use Money\Money;

/**
 * @property-read int $ID
 * @property-read string $TITLE
 * @property-read string $COMPANY_TYPE
 * @property-read File|null $LOGO
 * @property-read string $ADDRESS
 * @property-read string $ADDRESS_2
 * @property-read string $ADDRESS_CITY
 * @property-read string $ADDRESS_POSTAL_CODE
 * @property-read string $ADDRESS_REGION
 * @property-read string $ADDRESS_PROVINCE
 * @property-read string $ADDRESS_COUNTRY
 * @property-read string $ADDRESS_COUNTRY_CODE
 * @property-read int $ADDRESS_LOC_ADDR_ID
 * @property-read string $ADDRESS_LEGAL
 * @property-read string $REG_ADDRESS
 * @property-read string $REG_ADDRESS_2
 * @property-read string $REG_ADDRESS_CITY
 * @property-read string $REG_ADDRESS_POSTAL_CODE
 * @property-read string $REG_ADDRESS_REGION
 * @property-read string $REG_ADDRESS_PROVINCE
 * @property-read string $REG_ADDRESS_COUNTRY
 * @property-read string $REG_ADDRESS_COUNTRY_CODE
 * @property-read int $REG_ADDRESS_LOC_ADDR_ID
 * @property-read string $BANKING_DETAILS
 * @property-read string $INDUSTRY
 * @property-read string $EMPLOYEES
 * @property-read Currency $CURRENCY_ID
 * @property-read Money|null $REVENUE
 * @property-read bool|null $OPENED
 * @property-read string $COMMENTS
 * @property-read bool|null $HAS_PHONE
 * @property-read bool|null $HAS_EMAIL
 * @property-read bool|null $HAS_IMOL
 * @property-read bool|null $IS_MY_COMPANY
 * @property-read int|null $ASSIGNED_BY_ID
 * @property-read int|null $CREATED_BY_ID
 * @property-read int|null $MODIFY_BY_ID
 * @property-read CarbonImmutable $DATE_CREATE
 * @property-read CarbonImmutable $DATE_MODIFY
 * @property-read int|null $CONTACT_ID
 * @property-read int|null $LEAD_ID
 * @property-read string|null $ORIGINATOR_ID
 * @property-read string|null $ORIGIN_ID
 * @property-read string|null $ORIGIN_VERSION
 * @property-read string|null $UTM_SOURCE
 * @property-read string|null $UTM_MEDIUM
 * @property-read string|null $UTM_CAMPAIGN
 * @property-read string|null $UTM_CONTENT
 * @property-read string|null $UTM_TERM
 * @property-read CarbonImmutable $LAST_ACTIVITY_TIME
 * @property-read int|null $LAST_ACTIVITY_BY
 * @property-read Phone[] $PHONE
 * @property-read Email[] $EMAIL
 * @property-read Website[] $WEB
 * @property-read InstantMessenger[] $IM
 * @property-read array $LINK
 */
class CompanyItemResult extends AbstractCrmItem
{
    /**
     * @param string $userfieldName
     *
     * @return mixed|null
     * @throws UserfieldNotFoundException
     */
    public function getUserfieldByFieldName(string $userfieldName)
    {
        return $this->getKeyWithUserfieldByFieldName($userfieldName);
    }
}