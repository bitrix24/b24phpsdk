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

namespace Bitrix24\SDK\Services\CRM\Requisites\Result;

use Bitrix24\SDK\Services\CRM\Common\Result\AbstractCrmItem;
use Carbon\CarbonImmutable;

/**
 * Class RequisiteBankdetailItemResult
 *
 * @property-read int $ID
 * @property-read int $ENTITY_ID
 * @property-read int $COUNTRY_ID
 * @property-read CarbonImmutable|null $DATE_CREATE
 * @property-read CarbonImmutable|null $DATE_MODIFY
 * @property-read int|null $CREATED_BY_ID
 * @property-read int|null $MODIFY_BY_ID
 * @property-read string $NAME
 * @property-read string $CODE
 * @property-read string $XML_ID
 * @property-read bool|null $ACTIVE
 * @property-read int|null $SORT
 * @property-read string|null $RQ_BANK_NAME
 * @property-read string|null $RQ_BANK_ADDR
 * @property-read string|null $RQ_BANK_CODE
 * @property-read string|null $RQ_BANK_ROUTE_NUM
 * @property-read string|null $RQ_BIK
 * @property-read string|null $RQ_CODEB
 * @property-read string|null $RQ_CODEG
 * @property-read string|null $RQ_RIB
 * @property-read string|null $RQ_MFO
 * @property-read string|null $RQ_ACC_NAME
 * @property-read string|null $RQ_ACC_TYPE
 * @property-read string|null $RQ_AGENCY_NAME
 * @property-read string|null $RQ_IIK
 * @property-read string|null $RQ_ACC_CURRENCY
 * @property-read string|null $RQ_ACC_NUM
 * @property-read string|null $RQ_COR_ACC_NUM
 * @property-read string|null $RQ_IBAN
 * @property-read string|null $RQ_SWIFT
 * @property-read string|null $RQ_BIC
 * @property-read string|null $COMMENTS
 * @property-read string|null $ORIGINATOR_ID
 */
class RequisiteBankdetailItemResult extends AbstractCrmItem
{
    /**
     *
     * @return mixed|null
     * @throws \Bitrix24\SDK\Services\CRM\Userfield\Exceptions\UserfieldNotFoundException
     */
    public function getUserfieldByFieldName(string $userfieldName): mixed
    {
        return $this->getKeyWithUserfieldByFieldName($userfieldName);
    }
}
