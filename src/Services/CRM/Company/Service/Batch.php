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

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AddedItemBatchResult;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Core\Result\UpdatedItemBatchResult;
use Bitrix24\SDK\Services\AbstractBatchService;
use Bitrix24\SDK\Services\CRM\Company\Result\CompanyItemResult;
use Generator;

#[ApiBatchServiceMetadata(new Scope(['crm']))]
class Batch extends AbstractBatchService
{
    /**
     * batch list method
     *
     * @param array $order - order of company items
     * @param array $filter = ['ID','TITLE','COMPANY_TYPE','LOGO','ADDRESS','ADDRESS_2','ADDRESS_CITY','ADDRESS_POSTAL_CODE','ADDRESS_REGION','ADDRESS_PROVINCE','ADDRESS_COUNTRY','ADDRESS_COUNTRY_CODE','ADDRESS_LOC_ADDR_ID','ADDRESS_LEGAL','REG_ADDRESS','REG_ADDRESS_2','REG_ADDRESS_CITY','REG_ADDRESS_POSTAL_CODE','REG_ADDRESS_REGION','REG_ADDRESS_PROVINCE','REG_ADDRESS_COUNTRY','REG_ADDRESS_COUNTRY_CODE','REG_ADDRESS_LOC_ADDR_ID','BANKING_DETAILS','INDUSTRY','EMPLOYEES','CURRENCY_ID','REVENUE','OPENED','COMMENTS','HAS_PHONE','HAS_EMAIL','HAS_IMOL','IS_MY_COMPANY','ASSIGNED_BY_ID','CREATED_BY_ID','MODIFY_BY_ID','DATE_CREATE','DATE_MODIFY','CONTACT_ID','LEAD_ID','ORIGINATOR_ID','ORIGIN_ID','ORIGIN_VERSION','UTM_SOURCE','UTM_MEDIUM','UTM_CAMPAIGN','UTM_CONTENT','UTM_TERM','LAST_ACTIVITY_TIME','LAST_ACTIVITY_BY','PHONE','EMAIL','WEB','IM','LINK']
     * @param array $select = ['ID','TITLE','COMPANY_TYPE','LOGO','ADDRESS','ADDRESS_2','ADDRESS_CITY','ADDRESS_POSTAL_CODE','ADDRESS_REGION','ADDRESS_PROVINCE','ADDRESS_COUNTRY','ADDRESS_COUNTRY_CODE','ADDRESS_LOC_ADDR_ID','ADDRESS_LEGAL','REG_ADDRESS','REG_ADDRESS_2','REG_ADDRESS_CITY','REG_ADDRESS_POSTAL_CODE','REG_ADDRESS_REGION','REG_ADDRESS_PROVINCE','REG_ADDRESS_COUNTRY','REG_ADDRESS_COUNTRY_CODE','REG_ADDRESS_LOC_ADDR_ID','BANKING_DETAILS','INDUSTRY','EMPLOYEES','CURRENCY_ID','REVENUE','OPENED','COMMENTS','HAS_PHONE','HAS_EMAIL','HAS_IMOL','IS_MY_COMPANY','ASSIGNED_BY_ID','CREATED_BY_ID','MODIFY_BY_ID','DATE_CREATE','DATE_MODIFY','CONTACT_ID','LEAD_ID','ORIGINATOR_ID','ORIGIN_ID','ORIGIN_VERSION','UTM_SOURCE','UTM_MEDIUM','UTM_CAMPAIGN','UTM_CONTENT','UTM_TERM','LAST_ACTIVITY_TIME','LAST_ACTIVITY_BY','PHONE','EMAIL','WEB','IM','LINK']
     * @param int|null $limit
     *
     * @return Generator<int, CompanyItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'crm.company.list',
        'https://apidocs.bitrix24.com/api-reference/crm/companies/crm-company-list.html',
        'Returns in batch mode a list of contacts'
    )]
    public function list(array $order, array $filter, array $select, ?int $limit = null): Generator
    {
        $this->log->debug(
            'list',
            [
                'order' => $order,
                'filter' => $filter,
                'select' => $select,
                'limit' => $limit,
            ]
        );
        foreach (
            $this->batch->getTraversableList(
                'crm.company.list',
                $order,
                $filter,
                $select,
                $limit
            ) as $key => $value
        ) {
            yield $key => new CompanyItemResult($value);
        }
    }

    /**
     * Batch adding companies
     *
     * @param array <int, array{
     *    ID?: int,
     *    TITLE?: string,
     *    COMPANY_TYPE?: string,
     *    LOGO?: string,
     *    ADDRESS?: string,
     *    ADDRESS_2?: string,
     *    ADDRESS_CITY?: string,
     *    ADDRESS_POSTAL_CODE?: string,
     *    ADDRESS_REGION?: string,
     *    ADDRESS_PROVINCE?: string,
     *    ADDRESS_COUNTRY?: string,
     *    ADDRESS_COUNTRY_CODE?: string,
     *    ADDRESS_LOC_ADDR_ID?: int,
     *    ADDRESS_LEGAL?: string,
     *    REG_ADDRESS?: string,
     *    REG_ADDRESS_2?: string,
     *    REG_ADDRESS_CITY?: string,
     *    REG_ADDRESS_POSTAL_CODE?: string,
     *    REG_ADDRESS_REGION?: string,
     *    REG_ADDRESS_PROVINCE?: string,
     *    REG_ADDRESS_COUNTRY?: string,
     *    REG_ADDRESS_COUNTRY_CODE?: string,
     *    REG_ADDRESS_LOC_ADDR_ID?: int,
     *    BANKING_DETAILS?: string,
     *    INDUSTRY?: string,
     *    EMPLOYEES?: string,
     *    CURRENCY_ID?: string,
     *    REVENUE?: string,
     *    OPENED?: string,
     *    COMMENTS?: string,
     *    HAS_PHONE?: string,
     *    HAS_EMAIL?: string,
     *    HAS_IMOL?: string,
     *    IS_MY_COMPANY?: string,
     *    ASSIGNED_BY_ID?: string,
     *    CREATED_BY_ID?: string,
     *    MODIFY_BY_ID?: string,
     *    DATE_CREATE?: string,
     *    DATE_MODIFY?: string,
     *    CONTACT_ID?: string,
     *    LEAD_ID?: string,
     *    ORIGINATOR_ID?: string,
     *    ORIGIN_ID?: string,
     *    ORIGIN_VERSION?: string,
     *    UTM_SOURCE?: string,
     *    UTM_MEDIUM?: string,
     *    UTM_CAMPAIGN?: string,
     *    UTM_CONTENT?: string,
     *    UTM_TERM?: string,
     *    LAST_ACTIVITY_TIME?: string,
     *    LAST_ACTIVITY_BY?: string,
     *    PHONE?: string,
     *    EMAIL?: string,
     *    WEB?: string,
     *    IM?: string,
     *    LINK?: string,
     *    }> $companies
     *
     * @return Generator<int, AddedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'crm.company.add',
        'https://training.bitrix24.com/rest_help/crm/contacts/crm_contact_add.php',
        'Add in batch mode a list of contacts'
    )]
    public function add(array $companies): Generator
    {
        $items = [];
        foreach ($companies as $company) {
            $items[] = [
                'fields' => $company,
            ];
        }
        foreach ($this->batch->addEntityItems('crm.company.add', $items) as $key => $item) {
            yield $key => new AddedItemBatchResult($item);
        }
    }

    /**
     * Batch update companies
     *
     * Update elements in array with structure
     * element_id => [  // company id
     *  'fields' => [], // company fields to update
     *  'params' => []
     * ]
     *
     * @param array<int, array> $entityItems
     * @return Generator<int, UpdatedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'crm.company.update',
        'https://apidocs.bitrix24.com/api-reference/crm/companies/crm-company-update.html',
        'Update in batch mode a list of contacts'
    )]
    public function update(array $entityItems): Generator
    {
        foreach ($this->batch->updateEntityItems('crm.company.update', $entityItems) as $key => $item) {
            yield $key => new UpdatedItemBatchResult($item);
        }
    }

    /**
     * Batch delete company items
     *
     * @param int[] $companyId
     *
     * @return Generator<int, DeletedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'crm.company.delete',
        'https://apidocs.bitrix24.com/api-reference/crm/companies/crm-company-delete.html',
        'Delete in batch mode a list of companies'
    )]
    public function delete(array $companyId): Generator
    {
        foreach ($this->batch->deleteEntityItems('crm.company.delete', $companyId) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }
}