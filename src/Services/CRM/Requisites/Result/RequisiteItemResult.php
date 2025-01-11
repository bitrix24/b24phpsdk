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

namespace Bitrix24\SDK\Services\CRM\Requisites\Result;

use Bitrix24\SDK\Services\CRM\Common\Result\AbstractCrmItem;
use Carbon\CarbonImmutable;

/**
 * @property-read int|null $ID
 * @property-read int|null $ENTITY_TYPE_ID
 * @property-read int|null $ENTITY_ID
 * @property-read int|null $PRESET_ID
 * @property-read CarbonImmutable|null $DATE_CREATE
 * @property-read CarbonImmutable|null $DATE_MODIFY
 * @property-read int|null $CREATED_BY_ID
 * @property-read int|null $MODIFY_BY_ID
 * @property-read string|null $NAME
 * @property-read string|null $CODE
 * @property-read string|null $XML_ID
 * @property-read string|null $ORIGINATOR_ID
 * @property-read bool|null $ACTIVE
 * @property-read bool|null $ADDRESS_ONLY
 * @property-read int|null $SORT
 * @property-read string|null $RQ_NAME
 * @property-read string|null $RQ_FIRST_NAME
 * @property-read string|null $RQ_LAST_NAME
 * @property-read string|null $RQ_SECOND_NAME
 * @property-read string|null $RQ_COMPANY_ID
 * @property-read string|null $RQ_COMPANY_NAME
 * @property-read string|null $RQ_COMPANY_FULL_NAME
 * @property-read string|null $RQ_COMPANY_REG_DATE
 * @property-read string|null $RQ_DIRECTOR
 * @property-read string|null $RQ_ACCOUNTANT
 * @property-read string|null $RQ_CEO_NAME
 * @property-read string|null $RQ_CEO_WORK_POS
 * @property-read string|null $RQ_CONTACT
 * @property-read string|null $RQ_EMAIL
 * @property-read string|null $RQ_PHONE
 * @property-read string|null $RQ_FAX
 * @property-read string|null $RQ_IDENT_TYPE
 * @property-read string|null $RQ_IDENT_DOC
 * @property-read string|null $RQ_IDENT_DOC_SER
 * @property-read string|null $RQ_IDENT_DOC_NUM
 * @property-read string|null $RQ_IDENT_DOC_PERS_NUM
 * @property-read string|null $RQ_IDENT_DOC_DATE
 * @property-read string|null $RQ_IDENT_DOC_ISSUED_BY
 * @property-read string|null $RQ_IDENT_DOC_DEP_CODE
 * @property-read string|null $RQ_INN
 * @property-read string|null $RQ_KPP
 * @property-read string|null $RQ_USRLE
 * @property-read string|null $RQ_IFNS
 * @property-read string|null $RQ_OGRN
 * @property-read string|null $RQ_OGRNIP
 * @property-read string|null $RQ_OKPO
 * @property-read string|null $RQ_OKTMO
 * @property-read string|null $RQ_OKVED
 * @property-read string|null $RQ_EDRPOU
 * @property-read string|null $RQ_DRFO
 * @property-read string|null $RQ_KBE
 * @property-read string|null $RQ_IIN
 * @property-read string|null $RQ_BIN
 * @property-read string|null $RQ_ST_CERT_SER
 * @property-read string|null $RQ_ST_CERT_NUM
 * @property-read string|null $RQ_ST_CERT_DATE
 * @property-read bool|null $RQ_VAT_PAYER
 * @property-read string|null $RQ_VAT_ID
 * @property-read string|null $RQ_VAT_CERT_SER
 * @property-read string|null $RQ_VAT_CERT_NUM
 * @property-read string|null $RQ_VAT_CERT_DATE
 * @property-read string|null $RQ_RESIDENCE_COUNTRY
 * @property-read string|null $RQ_BASE_DOC
 * @property-read string|null $RQ_REGON
 * @property-read string|null $RQ_KRS
 * @property-read string|null $RQ_PESEL
 * @property-read string|null $RQ_LEGAL_FORM
 * @property-read string|null $RQ_SIRET
 * @property-read string|null $RQ_SIREN
 * @property-read string|null $RQ_CAPITAL
 * @property-read string|null $RQ_RCS
 * @property-read string|null $RQ_CNPJ
 * @property-read string|null $RQ_STATE_REG
 * @property-read string|null $RQ_MNPL_REG
 * @property-read string|null $RQ_CPF
 */
class RequisiteItemResult extends AbstractCrmItem
{
    public function getUserfieldByFieldName(string $userfieldName): mixed
    {
        return $this->getKeyWithUserfieldByFieldName($userfieldName);
    }
}
