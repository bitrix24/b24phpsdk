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

namespace Bitrix24\SDK\Services\CRM\Requisites\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\AddedItemResult;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Core\Result\FieldsResult;
use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\CRM\Lead\Result\LeadResult;
use Bitrix24\SDK\Services\CRM\Requisites\Result\RequisiteResult;
use Bitrix24\SDK\Services\CRM\Requisites\Result\RequisitesResult;
use Bitrix24\SDK\Services\CRM\Requisites\Service\Batch;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['crm']))]
class Requisite extends AbstractService
{
    public Batch $batch;

    public function __construct(Batch $batch, CoreInterface $core, LoggerInterface $log)
    {
        parent::__construct($core, $log);
        $this->batch = $batch;
    }

    /**
     * Returns the description of the requisite fields, including user fields.
     *
     * @link https://training.bitrix24.com/rest_help/crm/requisite/crm_requisite_fields.php
     *
     * @return FieldsResult
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.requisite.fields',
        'https://training.bitrix24.com/rest_help/crm/requisite/crm_requisite_fields.php',
        'Returns the description of the requisite fields, including user fields.'
    )]
    public function fields(): FieldsResult
    {
        return new FieldsResult($this->core->call('crm.requisite.fields'));
    }

    /**
     * Get list of requisite items.
     *
     * @link https://training.bitrix24.com/rest_help/crm/requisite/crm_requisite_list.php
     *
     * @param array $order - order items
     * @param array{
     * ID?: int,
     * ENTITY_TYPE_ID?: int,
     * ENTITY_ID?: int,
     * PRESET_ID?: int,
     * DATE_CREATE?: string,
     * DATE_MODIFY?: string,
     * CREATED_BY_ID?: string,
     * MODIFY_BY_ID?: string,
     * NAME?: string,
     * CODE?: string,
     * XML_ID?: string,
     * ORIGINATOR_ID?: string,
     * ACTIVE?: string,
     * ADDRESS_ONLY?: string,
     * SORT?: int,
     * RQ_NAME?: string,
     * RQ_FIRST_NAME?: string,
     * RQ_LAST_NAME?: string,
     * RQ_SECOND_NAME?: string,
     * RQ_COMPANY_ID?: string,
     * RQ_COMPANY_NAME?: string,
     * RQ_COMPANY_FULL_NAME?: string,
     * RQ_COMPANY_REG_DATE?: string,
     * RQ_DIRECTOR?: string,
     * RQ_ACCOUNTANT?: string,
     * RQ_CEO_NAME?: string,
     * RQ_CEO_WORK_POS?: string,
     * RQ_CONTACT?: string,
     * RQ_EMAIL?: string,
     * RQ_PHONE?: string,
     * RQ_FAX?: string,
     * RQ_IDENT_TYPE?: string,
     * RQ_IDENT_DOC?: string,
     * RQ_IDENT_DOC_SER?: string,
     * RQ_IDENT_DOC_NUM?: string,
     * RQ_IDENT_DOC_PERS_NUM?: string,
     * RQ_IDENT_DOC_DATE?: string,
     * RQ_IDENT_DOC_ISSUED_BY?: string,
     * RQ_IDENT_DOC_DEP_CODE?: string,
     * RQ_INN?: string,
     * RQ_KPP?: string,
     * RQ_USRLE?: string,
     * RQ_IFNS?: string,
     * RQ_OGRN?: string,
     * RQ_OGRNIP?: string,
     * RQ_OKPO?: string,
     * RQ_OKTMO?: string,
     * RQ_OKVED?: string,
     * RQ_EDRPOU?: string,
     * RQ_DRFO?: string,
     * RQ_KBE?: string,
     * RQ_IIN?: string,
     * RQ_BIN?: string,
     * RQ_ST_CERT_SER?: string,
     * RQ_ST_CERT_NUM?: string,
     * RQ_ST_CERT_DATE?: string,
     * RQ_VAT_PAYER?: string,
     * RQ_VAT_ID?: string,
     * RQ_VAT_CERT_SER?: string,
     * RQ_VAT_CERT_NUM?: string,
     * RQ_VAT_CERT_DATE?: string,
     * RQ_RESIDENCE_COUNTRY?: string,
     * RQ_BASE_DOC?: string,
     * RQ_REGON?: string,
     * RQ_KRS?: string,
     * RQ_PESEL?: string,
     * RQ_LEGAL_FORM?: string,
     * RQ_SIRET?: string,
     * RQ_SIREN?: string,
     * RQ_CAPITAL?: string,
     * RQ_RCS?: string,
     * RQ_CNPJ?: string,
     * RQ_STATE_REG?: string,
     * RQ_MNPL_REG?: string,
     * RQ_CPF?: string,
     * } $filter
     * @param array $select = ['ID','ENTITY_TYPE_ID','ENTITY_ID','PRESET_ID','DATE_CREATE','DATE_MODIFY','CREATED_BY_ID','MODIFY_BY_ID','NAME','CODE','XML_ID','ORIGINATOR_ID','ACTIVE','ADDRESS_ONLY','SORT','RQ_NAME','RQ_FIRST_NAME','RQ_LAST_NAME','RQ_SECOND_NAME','RQ_COMPANY_ID','RQ_COMPANY_NAME','RQ_COMPANY_FULL_NAME','RQ_COMPANY_REG_DATE','RQ_DIRECTOR','RQ_ACCOUNTANT','RQ_CEO_NAME','RQ_CEO_WORK_POS','RQ_CONTACT','RQ_EMAIL','RQ_PHONE','RQ_FAX','RQ_IDENT_TYPE','RQ_IDENT_DOC','RQ_IDENT_DOC_SER','RQ_IDENT_DOC_NUM','RQ_IDENT_DOC_PERS_NUM','RQ_IDENT_DOC_DATE','RQ_IDENT_DOC_ISSUED_BY','RQ_IDENT_DOC_DEP_CODE','RQ_INN','RQ_KPP','RQ_USRLE','RQ_IFNS','RQ_OGRN','RQ_OGRNIP','RQ_OKPO','RQ_OKTMO','RQ_OKVED','RQ_EDRPOU','RQ_DRFO','RQ_KBE','RQ_IIN','RQ_BIN','RQ_ST_CERT_SER','RQ_ST_CERT_NUM','RQ_ST_CERT_DATE','RQ_VAT_PAYER','RQ_VAT_ID','RQ_VAT_CERT_SER','RQ_VAT_CERT_NUM','RQ_VAT_CERT_DATE','RQ_RESIDENCE_COUNTRY','RQ_BASE_DOC','RQ_REGON','RQ_KRS','RQ_PESEL','RQ_LEGAL_FORM','RQ_SIRET','RQ_SIREN','RQ_CAPITAL','RQ_RCS','RQ_CNPJ','RQ_STATE_REG','RQ_MNPL_REG','RQ_CPF']
     * @param integer $startItem - entity number to start from (usually returned in 'next' field of previous 'crm.requisite.list' API call)
     *
     * @return RequisitesResult
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.requisite.list',
        'https://training.bitrix24.com/rest_help/crm/requisite/crm_requisite_list.php',
        'Get list of requisite items.'
    )]
    public function list(array $order, array $filter, array $select, int $startItem = 0): RequisitesResult
    {
        return new RequisitesResult(
            $this->core->call(
                'crm.requisite.list',
                [
                    'order' => $order,
                    'filter' => $filter,
                    'select' => $select,
                    'start' => $startItem,
                ]
            )
        );
    }

    /**
     * Add new requisite
     *
     * @link https://training.bitrix24.com/rest_help/crm/requisite/crm_requisite.add.php
     *
     * @param array{
     *   ID?: int,
     *   TITLE?: string,
     *   HONORIFIC?: string,
     *   NAME?: string,
     *   SECOND_NAME?: string,
     *   LAST_NAME?: string,
     *   BIRTHDATE?: string,
     *   COMPANY_TITLE?: string,
     *   SOURCE_ID?: string,
     *   SOURCE_DESCRIPTION?: string,
     *   STATUS_ID?: string,
     *   STATUS_DESCRIPTION?: string,
     *   STATUS_SEMANTIC_ID?: string,
     *   POST?: string,
     *   ADDRESS?: string,
     *   ADDRESS_2?: string,
     *   ADDRESS_CITY?: string,
     *   ADDRESS_POSTAL_CODE?: string,
     *   ADDRESS_REGION?: string,
     *   ADDRESS_PROVINCE?: string,
     *   ADDRESS_COUNTRY?: string,
     *   ADDRESS_COUNTRY_CODE?: string,
     *   ADDRESS_LOC_ADDR_ID?: int,
     *   CURRENCY_ID?: string,
     *   OPPORTUNITY?: string,
     *   IS_MANUAL_OPPORTUNITY?: string,
     *   OPENED?: string,
     *   COMMENTS?: string,
     *   HAS_PHONE?: string,
     *   HAS_EMAIL?: string,
     *   HAS_IMOL?: string,
     *   ASSIGNED_BY_ID?: string,
     *   CREATED_BY_ID?: string,
     *   MODIFY_BY_ID?: string,
     *   MOVED_BY_ID?: string,
     *   DATE_CREATE?: string,
     *   DATE_MODIFY?: string,
     *   MOVED_TIME?: string,
     *   COMPANY_ID?: string,
     *   CONTACT_ID?: string,
     *   CONTACT_IDS?: string,
     *   IS_RETURN_CUSTOMER?: string,
     *   DATE_CLOSED?: string,
     *   ORIGINATOR_ID?: string,
     *   ORIGIN_ID?: string,
     *   UTM_SOURCE?: string,
     *   UTM_MEDIUM?: string,
     *   UTM_CAMPAIGN?: string,
     *   UTM_CONTENT?: string,
     *   UTM_TERM?: string,
     *   PHONE?: string,
     *   EMAIL?: string,
     *   WEB?: string,
     *   IM?: string,
     *   LINK?: string
     *   } $fields
     * @return AddedItemResult
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.requisite.add',
        'https://training.bitrix24.com/rest_help/crm/requisite/crm_requisite.add.php',
        'Method adds new requisite'
    )]
    public function add(
        int $entityId,
        int $entityTypeId,
        int $requisitePresetId,
        string $requisiteName,
        array $fields
    ): AddedItemResult {
        $fields['ENTITY_TYPE_ID'] = $entityTypeId;
        $fields['ENTITY_ID'] = $entityId;
        $fields['PRESET_ID'] = $requisitePresetId;
        $fields['NAME'] = $requisiteName;

        return new AddedItemResult(
            $this->core->call(
                'crm.requisite.add',
                [
                    'fields' => $fields,
                ]
            )
        );
    }

    /**
     * Returns a requisite by the requisite id.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/requisites/universal/crm-requisite-get.html
     *
     * @param non-negative-int $id
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.requisite.get',
        'https://apidocs.bitrix24.com/api-reference/crm/requisites/universal/crm-requisite-get.html',
        'Returns a requisite by the requisite id.'
    )]
    public function get(int $id): RequisiteResult
    {
        $this->guardPositiveId($id);
        return new RequisiteResult($this->core->call('crm.requisite.get', ['id' => $id]));
    }

    /**
     * Delete Requisite and Related Objects.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/requisites/universal/crm-requisite-delete.html
     *
     * @param non-negative-int $id
     *
     * @return DeletedItemResult
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.requisite.delete',
        'https://apidocs.bitrix24.com/api-reference/crm/requisites/universal/crm-requisite-delete.html',
        'Delete Requisite and Related Objects'
    )]
    public function delete(int $id): DeletedItemResult
    {
        $this->guardPositiveId($id);
        return new DeletedItemResult(
            $this->core->call(
                'crm.requisite.delete',
                [
                    'id' => $id,
                ]
            )
        );
    }

    /**
     * Updates the specified (existing) requisite.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/requisites/universal/crm-requisite-update.html
     *
     * @param int $id
     * @param array{
     * ID?: int,
     * TITLE?: string,
     * HONORIFIC?: string,
     * NAME?: string,
     * SECOND_NAME?: string,
     * LAST_NAME?: string,
     * BIRTHDATE?: string,
     * COMPANY_TITLE?: string,
     * SOURCE_ID?: string,
     * SOURCE_DESCRIPTION?: string,
     * STATUS_ID?: string,
     * STATUS_DESCRIPTION?: string,
     * STATUS_SEMANTIC_ID?: string,
     * POST?: string,
     * ADDRESS?: string,
     * ADDRESS_2?: string,
     * ADDRESS_CITY?: string,
     * ADDRESS_POSTAL_CODE?: string,
     * ADDRESS_REGION?: string,
     * ADDRESS_PROVINCE?: string,
     * ADDRESS_COUNTRY?: string,
     * ADDRESS_COUNTRY_CODE?: string,
     * ADDRESS_LOC_ADDR_ID?: int,
     * CURRENCY_ID?: string,
     * OPPORTUNITY?: string,
     * IS_MANUAL_OPPORTUNITY?: string,
     * OPENED?: string,
     * COMMENTS?: string,
     * HAS_PHONE?: string,
     * HAS_EMAIL?: string,
     * HAS_IMOL?: string,
     * ASSIGNED_BY_ID?: string,
     * CREATED_BY_ID?: string,
     * MODIFY_BY_ID?: string,
     * MOVED_BY_ID?: string,
     * DATE_CREATE?: string,
     * DATE_MODIFY?: string,
     * MOVED_TIME?: string,
     * COMPANY_ID?: string,
     * CONTACT_ID?: string,
     * CONTACT_IDS?: string,
     * IS_RETURN_CUSTOMER?: string,
     * DATE_CLOSED?: string,
     * ORIGINATOR_ID?: string,
     * ORIGIN_ID?: string,
     * UTM_SOURCE?: string,
     * UTM_MEDIUM?: string,
     * UTM_CAMPAIGN?: string,
     * UTM_CONTENT?: string,
     * UTM_TERM?: string,
     * PHONE?: string,
     * EMAIL?: string,
     * WEB?: string,
     * IM?: string,
     * LINK?: string
     * } $fields
     *
     * @return UpdatedItemResult
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.requisite.update',
        'https://apidocs.bitrix24.com/api-reference/crm/requisites/universal/crm-requisite-update.html',
        'Updates the specified (existing) requisite.'
    )]
    public function update(int $id, array $fields): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call(
                'crm.requisite.update',
                [
                    'id' => $id,
                    'fields' => $fields,
                ]
            )
        );
    }
}
