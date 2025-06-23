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

namespace Bitrix24\SDK\Services\CRM\Requisite\Service;

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
use Bitrix24\SDK\Services\CRM\Requisite\Result\RequisiteBankdetailResult;
use Bitrix24\SDK\Services\CRM\Requisite\Result\RequisiteBankdetailsResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['crm']))]
class RequisiteBankdetail extends AbstractService
{
    /**
     * Add a new Bank Detail
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/requisites/bank-detail/crm-requisite-bank-detail-add.html
     *
     * @param array{
     *   ID?: int,
     *   ENTITY_TYPE_ID?: int,
     *   ENTITY_ID?: int,
     *   COUNTRY_ID?: int,
     *   DATE_CREATE?: string,
     *   DATE_MODIFY?: string,
     *   CREATED_BY_ID?: int,
     *   MODIFY_BY_ID?: int, 
     *   NAME?: string,
     *   CODE?: string,
     *   XML_ID?: string,
     *   ACTIVE?: bool,
     *   SORT?: int,
     *   RQ_BANK_NAME?: string,
     *   RQ_BANK_ADDR?: string,
     *   RQ_BANK_CODE?: string,
     *   RQ_BANK_ROUTE_NUM?: string,
     *   RQ_BIK?: string,
     *   RQ_CODEB?: string,
     *   RQ_CODEG?: string,
     *   RQ_RIB?: string,
     *   RQ_MFO?: string,
     *   RQ_ACC_NAME?: string,
     *   RQ_ACC_TYPE?: string,
     *   RQ_AGENCY_NAME?: string,
     *   RQ_IIK?: string,
     *   RQ_ACC_CURRENCY?: string,
     *   RQ_COR_ACC_NUM?: string,
     *   RQ_IBAN?: string,
     *   RQ_SWIFT?: string,
     *   RQ_BIC?: string,
     *   COMMENTS?: string,
     *   ORIGINATOR_ID?: string,
     * } $fields
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.requisite.bankdetail.add',
        'https://apidocs.bitrix24.com/api-reference/crm/requisites/bank-detail/crm-requisite-bank-detail-add.html',
        'Add a new Bank Detail'
    )]
    public function add(array $fields): AddedItemResult
    {
        return new AddedItemResult(
            $this->core->call(
                'crm.requisite.bankdetail.add',
                [
                    'fields' => $fields
                ]
            )
        );
    }

    /**
     * Deletes the specified bank detail.
     *
     * @link hhttps://apidocs.bitrix24.com/api-reference/crm/requisites/bank-detail/crm-requisite-bank-detail-delete.html
     *
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.requisite.bankdetail.delete',
        'https://apidocs.bitrix24.com/api-reference/crm/requisites/bank-detail/crm-requisite-bank-detail-delete.html',
        'Deletes the specified bank detail'
    )]
    public function delete(int $id): DeletedItemResult
    {
        return new DeletedItemResult(
            $this->core->call(
                'crm.requisite.bankdetail.delete',
                [
                    'id' => $id,
                ]
            )
        );
    }

    /**
     * Returns the description of the bank detail fields, including user fields.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/requisites/bank-detail/crm-requisite-bank-detail-fields.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.requisite.bankdetail.fields',
        'https://apidocs.bitrix24.com/api-reference/crm/requisites/bank-detail/crm-requisite-bank-detail-fields.html',
        'Returns the description of the bank detail fields, including user fields.'
    )]
    public function fields(): FieldsResult
    {
        return new FieldsResult($this->core->call('crm.requisite.bankdetail.fields'));
    }

    /**
     * Returns a bank detail by identifier.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/requisites/bank-detail/crm-requisite-bank-detail-get.html
     *
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.requisite.bankdetail.get',
        'https://apidocs.bitrix24.com/api-reference/crm/requisites/bank-detail/crm-requisite-bank-detail-get.html',
        'Returns a bank detail by identifier.'
    )]
    public function get(int $id): RequisiteBankdetailResult
    {
        return new RequisiteBankdetailResult($this->core->call('crm.requisite.bankdetail.get', ['id' => $id]));
    }

    /**
     * Get list of bank detail items.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/requisites/bank-detail/crm-requisite-bank-detail-list.html
     *
     * @param array   $order     - order of bank detail items
     * @param array   $filter    - filter array
     * @param array   $select    = ['ID','ENTITY_TYPE_ID','ENTITY_ID','COUNTRY_ID','DATE_CREATE','DATE_MODIFY','CREATED_BY_ID','MODIFY_BY_ID','NAME','CODE','XML_ID','ACTIVE','SORT','RQ_BANK_NAME','RQ_BANK_ADDR','RQ_BANK_CODE','RQ_BANK_ROUTE_NUM','RQ_BIK','RQ_CODEB','RQ_CODEG','RQ_RIB','RQ_MFO','RQ_ACC_NAME','RQ_ACC_TYPE','RQ_AGENCY_NAME','RQ_IIK','RQ_ACC_CURRENCY','RQ_COR_ACC_NUM','RQ_IBAN','RQ_SWIFT','RQ_BIC','COMMENTS','ORIGINATOR_ID']
     * @param integer $startItem - entity number to start from (usually returned in 'next' field of previous 'crm.requisite.bankdetail.list' API call)
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.requisite.bankdetail.list',
        'https://apidocs.bitrix24.com/api-reference/crm/requisites/bank-detail/crm-requisite-bank-detail-list.html',
        'Get list of bank detail items.'
    )]
    public function list(array $order, array $filter, array $select, int $startItem = 0): RequisiteBankdetailsResult
    {
        return new RequisiteBankdetailsResult(
            $this->core->call(
                'crm.requisite.bankdetail.list',
                [
                    'order'  => $order,
                    'filter' => $filter,
                    'select' => $select,
                    'start'  => $startItem,
                ]
            )
        );
    }

    /**
     * Updates the specified (existing) requisite.bankdetail.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/requisites/bank-detail/crm-requisite-bank-detail-update.html
     *
     * @param array{
     *   ID?: int,
     *   ENTITY_TYPE_ID?: int,
     *   ENTITY_ID?: int,
     *   COUNTRY_ID?: int,
     *   DATE_CREATE?: string,
     *   DATE_MODIFY?: string,
     *   CREATED_BY_ID?: int,
     *   MODIFY_BY_ID?: int, 
     *   NAME?: string,
     *   CODE?: string,
     *   XML_ID?: string,
     *   ACTIVE?: bool,
     *   SORT?: int,
     *   RQ_BANK_NAME?: string,
     *   RQ_BANK_ADDR?: string,
     *   RQ_BANK_CODE?: string,
     *   RQ_BANK_ROUTE_NUM?: string,
     *   RQ_BIK?: string,
     *   RQ_CODEB?: string,
     *   RQ_CODEG?: string,
     *   RQ_RIB?: string,
     *   RQ_MFO?: string,
     *   RQ_ACC_NAME?: string,
     *   RQ_ACC_TYPE?: string,
     *   RQ_AGENCY_NAME?: string,
     *   RQ_IIK?: string,
     *   RQ_ACC_CURRENCY?: string,
     *   RQ_COR_ACC_NUM?: string,
     *   RQ_IBAN?: string,
     *   RQ_SWIFT?: string,
     *   RQ_BIC?: string,
     *   COMMENTS?: string,
     *   ORIGINATOR_ID?: string,
     *   }        $fields
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.requisite.bankdetail.update',
        'https://apidocs.bitrix24.com/api-reference/crm/requisites/bank-detail/crm-requisite-bank-detail-update.html',
        'Updates the specified (existing) requisite.bankdetail.'
    )]
    public function update(int $id, array $fields): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call(
                'crm.requisite.bankdetail.update',
                [
                    'id'     => $id,
                    'fields' => $fields
                ]
            )
        );
    }

    /**
     * Count quotes by filter
     *
     * @param array{
     *   ID?: int,
     *   ENTITY_TYPE_ID?: int,
     *   ENTITY_ID?: int,
     *   COUNTRY_ID?: int,
     *   DATE_CREATE?: string,
     *   DATE_MODIFY?: string,
     *   CREATED_BY_ID?: int,
     *   MODIFY_BY_ID?: int, 
     *   NAME?: string,
     *   CODE?: string,
     *   XML_ID?: string,
     *   ACTIVE?: bool,
     *   SORT?: int,
     *   RQ_BANK_NAME?: string,
     *   RQ_BANK_ADDR?: string,
     *   RQ_BANK_CODE?: string,
     *   RQ_BANK_ROUTE_NUM?: string,
     *   RQ_BIK?: string,
     *   RQ_CODEB?: string,
     *   RQ_CODEG?: string,
     *   RQ_RIB?: string,
     *   RQ_MFO?: string,
     *   RQ_ACC_NAME?: string,
     *   RQ_ACC_TYPE?: string,
     *   RQ_AGENCY_NAME?: string,
     *   RQ_IIK?: string,
     *   RQ_ACC_CURRENCY?: string,
     *   RQ_COR_ACC_NUM?: string,
     *   RQ_IBAN?: string,
     *   RQ_SWIFT?: string,
     *   RQ_BIC?: string,
     *   COMMENTS?: string,
     *   ORIGINATOR_ID?: string,
     *   } $filter
     *
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     * @throws \Bitrix24\SDK\Core\Exceptions\TransportException
     */
    public function countByFilter(array $filter = []): int
    {
        return $this->list([], $filter, ['ID'], 1)->getCoreResponse()->getResponseData()->getPagination()->getTotal();
    }
}
