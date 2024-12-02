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

namespace Bitrix24\SDK\Services\CRM\Company\Service;

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
use Bitrix24\SDK\Services\CRM\Company\Result\CompaniesResult;
use Bitrix24\SDK\Services\CRM\Company\Result\CompanyResult;
use Psr\Log\LoggerInterface;
use Bitrix24\SDK\Attributes\ApiEndpointMetadata;

#[ApiServiceMetadata(new Scope(['crm']))]
class Company extends AbstractService
{
    public Batch $batch;

    /**
     * Deal constructor.
     *
     * @param Batch $batch
     * @param CoreInterface $core
     * @param LoggerInterface $log
     */
    public function __construct(Batch $batch, CoreInterface $core, LoggerInterface $log)
    {
        parent::__construct($core, $log);
        $this->batch = $batch;
    }

    /**
     * The method crm.company.fields returns the description of company fields, including custom fields.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/companies/crm-company-fields.html
     *
     * @return FieldsResult
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.company.fields',
        'https://apidocs.bitrix24.com/api-reference/crm/companies/crm-company-fields.html',
        'The method crm.company.fields returns the description of company fields, including custom fields.'
    )]
    public function fields(): FieldsResult
    {
        return new FieldsResult($this->core->call('crm.company.fields'));
    }

    /**
     * Add new company
     *
     * @param array{
     *   ID?: int,
     *   TITLE?: string,
     *   COMPANY_TYPE?: string,
     *   LOGO?: string,
     *   ADDRESS?: string,
     *   ADDRESS_2?: string,
     *   ADDRESS_CITY?: string,
     *   ADDRESS_POSTAL_CODE?: string,
     *   ADDRESS_REGION?: string,
     *   ADDRESS_PROVINCE?: string,
     *   ADDRESS_COUNTRY?: string,
     *   ADDRESS_COUNTRY_CODE?: string,
     *   ADDRESS_LOC_ADDR_ID?: int,
     *   ADDRESS_LEGAL?: string,
     *   REG_ADDRESS?: string,
     *   REG_ADDRESS_2?: string,
     *   REG_ADDRESS_CITY?: string,
     *   REG_ADDRESS_POSTAL_CODE?: string,
     *   REG_ADDRESS_REGION?: string,
     *   REG_ADDRESS_PROVINCE?: string,
     *   REG_ADDRESS_COUNTRY?: string,
     *   REG_ADDRESS_COUNTRY_CODE?: string,
     *   REG_ADDRESS_LOC_ADDR_ID?: int,
     *   BANKING_DETAILS?: string,
     *   INDUSTRY?: string,
     *   EMPLOYEES?: string,
     *   CURRENCY_ID?: string,
     *   REVENUE?: string,
     *   OPENED?: string,
     *   COMMENTS?: string,
     *   HAS_PHONE?: string,
     *   HAS_EMAIL?: string,
     *   HAS_IMOL?: string,
     *   IS_MY_COMPANY?: string,
     *   ASSIGNED_BY_ID?: string,
     *   CREATED_BY_ID?: string,
     *   MODIFY_BY_ID?: string,
     *   DATE_CREATE?: string,
     *   DATE_MODIFY?: string,
     *   CONTACT_ID?: string,
     *   LEAD_ID?: string,
     *   ORIGINATOR_ID?: string,
     *   ORIGIN_ID?: string,
     *   ORIGIN_VERSION?: string,
     *   UTM_SOURCE?: string,
     *   UTM_MEDIUM?: string,
     *   UTM_CAMPAIGN?: string,
     *   UTM_CONTENT?: string,
     *   UTM_TERM?: string,
     *   LAST_ACTIVITY_TIME?: string,
     *   LAST_ACTIVITY_BY?: string,
     *   PHONE?: string,
     *   EMAIL?: string,
     *   WEB?: string,
     *   IM?: string,
     *   LINK?: string,
     *   } $fields
     *
     * @param array{
     *   REGISTER_SONET_EVENT?: string
     *   } $params
     *
     * @return AddedItemResult
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.company.add',
        'https://apidocs.bitrix24.com/api-reference/crm/companies/crm-company-add.html',
        'Add new company'
    )]
    public function add(array $fields, array $params = []): AddedItemResult
    {
        return new AddedItemResult(
            $this->core->call(
                'crm.company.add',
                [
                    'fields' => $fields,
                    'params' => $params,
                ]
            )
        );
    }

    /**
     * Returns a company by the company ID.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/companies/crm-company-get.html
     *
     * @param int $id
     *
     * @return CompanyResult
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.company.get',
        'https://apidocs.bitrix24.com/api-reference/crm/companies/crm-company-get.html',
        'The method crm.company.get returns a company by its identifier.'
    )]
    public function get(int $id): CompanyResult
    {
        return new CompanyResult($this->core->call('crm.company.get', ['id' => $id]));
    }

    /**
     * Deletes the specified deal and all the associated objects.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/companies/crm-company-delete.html
     *
     * @param int $id
     *
     * @return DeletedItemResult
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.company.delete',
        'https://apidocs.bitrix24.com/api-reference/crm/companies/crm-company-delete.html',
        'Delete deal'
    )]
    public function delete(int $id): DeletedItemResult
    {
        return new DeletedItemResult(
            $this->core->call(
                'crm.company.delete',
                [
                    'id' => $id,
                ]
            )
        );
    }

    /**
     * Get list of company items.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/companies/crm-company-list.html
     *
     * @param array $order - order of company items
     * @param array $filter = ['ID','TITLE','COMPANY_TYPE','LOGO','ADDRESS','ADDRESS_2','ADDRESS_CITY','ADDRESS_POSTAL_CODE','ADDRESS_REGION','ADDRESS_PROVINCE','ADDRESS_COUNTRY','ADDRESS_COUNTRY_CODE','ADDRESS_LOC_ADDR_ID','ADDRESS_LEGAL','REG_ADDRESS','REG_ADDRESS_2','REG_ADDRESS_CITY','REG_ADDRESS_POSTAL_CODE','REG_ADDRESS_REGION','REG_ADDRESS_PROVINCE','REG_ADDRESS_COUNTRY','REG_ADDRESS_COUNTRY_CODE','REG_ADDRESS_LOC_ADDR_ID','BANKING_DETAILS','INDUSTRY','EMPLOYEES','CURRENCY_ID','REVENUE','OPENED','COMMENTS','HAS_PHONE','HAS_EMAIL','HAS_IMOL','IS_MY_COMPANY','ASSIGNED_BY_ID','CREATED_BY_ID','MODIFY_BY_ID','DATE_CREATE','DATE_MODIFY','CONTACT_ID','LEAD_ID','ORIGINATOR_ID','ORIGIN_ID','ORIGIN_VERSION','UTM_SOURCE','UTM_MEDIUM','UTM_CAMPAIGN','UTM_CONTENT','UTM_TERM','LAST_ACTIVITY_TIME','LAST_ACTIVITY_BY','PHONE','EMAIL','WEB','IM','LINK']
     * @param array $select = ['ID','TITLE','COMPANY_TYPE','LOGO','ADDRESS','ADDRESS_2','ADDRESS_CITY','ADDRESS_POSTAL_CODE','ADDRESS_REGION','ADDRESS_PROVINCE','ADDRESS_COUNTRY','ADDRESS_COUNTRY_CODE','ADDRESS_LOC_ADDR_ID','ADDRESS_LEGAL','REG_ADDRESS','REG_ADDRESS_2','REG_ADDRESS_CITY','REG_ADDRESS_POSTAL_CODE','REG_ADDRESS_REGION','REG_ADDRESS_PROVINCE','REG_ADDRESS_COUNTRY','REG_ADDRESS_COUNTRY_CODE','REG_ADDRESS_LOC_ADDR_ID','BANKING_DETAILS','INDUSTRY','EMPLOYEES','CURRENCY_ID','REVENUE','OPENED','COMMENTS','HAS_PHONE','HAS_EMAIL','HAS_IMOL','IS_MY_COMPANY','ASSIGNED_BY_ID','CREATED_BY_ID','MODIFY_BY_ID','DATE_CREATE','DATE_MODIFY','CONTACT_ID','LEAD_ID','ORIGINATOR_ID','ORIGIN_ID','ORIGIN_VERSION','UTM_SOURCE','UTM_MEDIUM','UTM_CAMPAIGN','UTM_CONTENT','UTM_TERM','LAST_ACTIVITY_TIME','LAST_ACTIVITY_BY','PHONE','EMAIL','WEB','IM','LINK']
     * @param int $startItem - entity number to start from (usually returned in 'next' field of previous 'crm.company.list' API call)
     *
     * @throws BaseException
     * @throws TransportException
     * @return CompaniesResult
     */
    #[ApiEndpointMetadata(
        'crm.company.list',
        'https://apidocs.bitrix24.com/api-reference/crm/companies/crm-company-list.html',
        'Get company list by filter'
    )]
    public function list(array $order = [], array $filter = [], array $select = [], int $startItem = 0): CompaniesResult
    {
        return new CompaniesResult(
            $this->core->call(
                'crm.company.list',
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
     * Updates the specified (existing) deal.
     *
     * @link https://training.bitrix24.com/rest_help/crm/deals/crm_deal_update.php
     *
     * @param int $id
     * @param array{
     *   TITLE?: string,
     *   COMPANY_TYPE?: string,
     *   LOGO?: string,
     *   ADDRESS?: string,
     *   ADDRESS_2?: string,
     *   ADDRESS_CITY?: string,
     *   ADDRESS_POSTAL_CODE?: string,
     *   ADDRESS_REGION?: string,
     *   ADDRESS_PROVINCE?: string,
     *   ADDRESS_COUNTRY?: string,
     *   ADDRESS_COUNTRY_CODE?: string,
     *   ADDRESS_LOC_ADDR_ID?: int,
     *   ADDRESS_LEGAL?: string,
     *   REG_ADDRESS?: string,
     *   REG_ADDRESS_2?: string,
     *   REG_ADDRESS_CITY?: string,
     *   REG_ADDRESS_POSTAL_CODE?: string,
     *   REG_ADDRESS_REGION?: string,
     *   REG_ADDRESS_PROVINCE?: string,
     *   REG_ADDRESS_COUNTRY?: string,
     *   REG_ADDRESS_COUNTRY_CODE?: string,
     *   REG_ADDRESS_LOC_ADDR_ID?: int,
     *   BANKING_DETAILS?: string,
     *   INDUSTRY?: string,
     *   EMPLOYEES?: string,
     *   CURRENCY_ID?: string,
     *   REVENUE?: string,
     *   OPENED?: string,
     *   COMMENTS?: string,
     *   HAS_PHONE?: string,
     *   HAS_EMAIL?: string,
     *   HAS_IMOL?: string,
     *   IS_MY_COMPANY?: string,
     *   ASSIGNED_BY_ID?: string,
     *   CREATED_BY_ID?: string,
     *   MODIFY_BY_ID?: string,
     *   DATE_CREATE?: string,
     *   DATE_MODIFY?: string,
     *   CONTACT_ID?: string,
     *   LEAD_ID?: string,
     *   ORIGINATOR_ID?: string,
     *   ORIGIN_ID?: string,
     *   ORIGIN_VERSION?: string,
     *   UTM_SOURCE?: string,
     *   UTM_MEDIUM?: string,
     *   UTM_CAMPAIGN?: string,
     *   UTM_CONTENT?: string,
     *   UTM_TERM?: string,
     *   LAST_ACTIVITY_TIME?: string,
     *   LAST_ACTIVITY_BY?: string,
     *   PHONE?: string,
     *   EMAIL?: string,
     *   WEB?: string,
     *   IM?: string,
     *   LINK?: string,
     *   } $fields
     *
     * @param array{
     *   REGISTER_SONET_EVENT?: string
     *   } $params
     *
     * @return UpdatedItemResult
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.company.update',
        'https://apidocs.bitrix24.com/api-reference/crm/companies/crm-company-update.html',
        'Update company by id'
    )]
    public function update(int $id, array $fields, array $params = []): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call(
                'crm.company.update',
                [
                    'id' => $id,
                    'fields' => $fields,
                    'params' => $params,
                ]
            )
        );
    }

    /**
     * @param array{
     *   ID?: int,
     *   TITLE?: string,
     *   COMPANY_TYPE?: string,
     *   LOGO?: string,
     *   ADDRESS?: string,
     *   ADDRESS_2?: string,
     *   ADDRESS_CITY?: string,
     *   ADDRESS_POSTAL_CODE?: string,
     *   ADDRESS_REGION?: string,
     *   ADDRESS_PROVINCE?: string,
     *   ADDRESS_COUNTRY?: string,
     *   ADDRESS_COUNTRY_CODE?: string,
     *   ADDRESS_LOC_ADDR_ID?: int,
     *   ADDRESS_LEGAL?: string,
     *   REG_ADDRESS?: string,
     *   REG_ADDRESS_2?: string,
     *   REG_ADDRESS_CITY?: string,
     *   REG_ADDRESS_POSTAL_CODE?: string,
     *   REG_ADDRESS_REGION?: string,
     *   REG_ADDRESS_PROVINCE?: string,
     *   REG_ADDRESS_COUNTRY?: string,
     *   REG_ADDRESS_COUNTRY_CODE?: string,
     *   REG_ADDRESS_LOC_ADDR_ID?: int,
     *   BANKING_DETAILS?: string,
     *   INDUSTRY?: string,
     *   EMPLOYEES?: string,
     *   CURRENCY_ID?: string,
     *   REVENUE?: string,
     *   OPENED?: string,
     *   COMMENTS?: string,
     *   HAS_PHONE?: string,
     *   HAS_EMAIL?: string,
     *   HAS_IMOL?: string,
     *   IS_MY_COMPANY?: string,
     *   ASSIGNED_BY_ID?: string,
     *   CREATED_BY_ID?: string,
     *   MODIFY_BY_ID?: string,
     *   DATE_CREATE?: string,
     *   DATE_MODIFY?: string,
     *   CONTACT_ID?: string,
     *   LEAD_ID?: string,
     *   ORIGINATOR_ID?: string,
     *   ORIGIN_ID?: string,
     *   ORIGIN_VERSION?: string,
     *   UTM_SOURCE?: string,
     *   UTM_MEDIUM?: string,
     *   UTM_CAMPAIGN?: string,
     *   UTM_CONTENT?: string,
     *   UTM_TERM?: string,
     *   LAST_ACTIVITY_TIME?: string,
     *   LAST_ACTIVITY_BY?: string,
     *   PHONE?: string,
     *   EMAIL?: string,
     *   WEB?: string,
     *   IM?: string,
     *   LINK?: string,
     *   } $filter
     * @return int
     * @throws BaseException
     * @throws TransportException
     */
    public function countByFilter(array $filter = []): int
    {
        return $this->list([], $filter, ['ID'], 1)->getCoreResponse()->getResponseData()->getPagination()->getTotal();
    }
}