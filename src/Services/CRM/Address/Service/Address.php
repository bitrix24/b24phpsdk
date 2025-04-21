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

namespace Bitrix24\SDK\Services\CRM\Address\Service;

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
use Bitrix24\SDK\Services\CRM\Address\Result\AddressResult;
use Bitrix24\SDK\Services\CRM\Address\Result\AddressesResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['crm']))]
class Address extends AbstractService
{
    public Batch $batch;

    /**
     * Address constructor.
     *
     * @param Batch           $batch
     * @param CoreInterface   $core
     * @param LoggerInterface $log
     */
    public function __construct(Batch $batch, CoreInterface $core, LoggerInterface $log)
    {
        parent::__construct($core, $log);
        $this->batch = $batch;
    }

    /**
     * add new address
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/requisites/addresses/crm-address-add.html
     *
     * @param array{
     *   TYPE_ID?: int,
     *   ENTITY_TYPE_ID?: int,
     *   ENTITY_ID?: int,
     *   ADDRESS_1?: string,
     *   ADDRESS_2?: string,
     *   CITY?: string,
     *   POSTAL_CODE?: string,
     *   REGION?: string,
     *   PROVINCE?: string,
     *   COUNTRY?: string,
     *   COUNTRY_CODE?: string,
     *   LOC_ADDR_ID?: int,
     *   } $fields
     *
     * @return AddedItemResult
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.address.add',
        'https://apidocs.bitrix24.com/api-reference/crm/requisites/addresses/crm-address-add.html',
        'Method adds new address'
    )]
    public function add(array $fields): AddedItemResult
    {
        return new AddedItemResult(
            $this->core->call(
                'crm.address.add',
                [
                    'fields' => $fields,
                ]
            )
        );
    }

    /**
     * Deletes the specified address.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/requisites/addresses/crm-address-delete.html
     *
     * @param int $typeId
     * @param int $entityTypeId
     * @param int $entityId
     *
     * @return DeletedItemResult
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.address.delete',
        'https://apidocs.bitrix24.com/api-reference/crm/requisites/addresses/crm-address-delete.html',
        'Deletes the specified address.'
    )]
    public function delete(int $typeId, int $entityTypeId, int $entityId): DeletedItemResult
    {
        return new DeletedItemResult(
            $this->core->call(
                'crm.address.delete',
                [
                    'fields' => [
                        'TYPE_ID' => $typeId,
                        'ENTITY_TYPE_ID' => $entityTypeId,
                        'ENTITY_ID' => $entityId,
                    ],
                ]
            )
        );
    }

    /**
     * Returns the description of the address fields.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/requisites/addresses/crm-address-fields.html
     *
     * @return FieldsResult
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.address.fields',
        'https://apidocs.bitrix24.com/api-reference/crm/requisites/addresses/crm-address-fields.html',
        'Returns the description of the address fields.'
    )]
    public function fields(): FieldsResult
    {
        return new FieldsResult($this->core->call('crm.address.fields'));
    }

    /**
     * Get list of address items.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/requisites/addresses/crm-address-list.html
     *
     * @param array   $order     - order of address items
     * @param array   $filter    - filter array
     * @param array   $select    = ['TYPE_ID','ENTITY_TYPE_ID','ENTITY_ID','ADDRESS_1','ADDRESS_2','CITY','POSTAL_CODE','REGION','PROVINCE','COUNTRY','COUNTRY_CODE','LOC_ADDR_ID','ANCHOR_TYPE_ID','ANCHOR_ID']
     * @param integer $startItem - entity number to start from (usually returned in 'next' field of previous 'crm.address.list' API call)
     *
     * @throws BaseException
     * @throws TransportException
     * @return AddressesResult
     */
    #[ApiEndpointMetadata(
        'crm.address.list',
        'https://apidocs.bitrix24.com/api-reference/crm/requisites/addresses/crm-address-list.html',
        'Get list of address items.'
    )]
    public function list(array $order, array $filter, array $select, int $startItem = 0): AddressesResult
    {
        return new AddressesResult(
            $this->core->call(
                'crm.address.list',
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
     * Updates the specified (existing) address.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/requisites/addresses/crm-address-update.html
     *
     * @param array{
     *   TYPE_ID?: int,
     *   ENTITY_TYPE_ID?: int,
     *   ENTITY_ID?: int,
     *   ADDRESS_1?: string,
     *   ADDRESS_2?: string,
     *   CITY?: string,
     *   POSTAL_CODE?: string,
     *   REGION?: string,
     *   PROVINCE?: string,
     *   COUNTRY?: string,
     *   COUNTRY_CODE?: string,
     *   LOC_ADDR_ID?: int,
     *   }        $fields
     *
     * @return UpdatedItemResult
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.address.update',
        'https://apidocs.bitrix24.com/api-reference/crm/requisites/addresses/crm-address-update.html',
        'Updates the specified (existing) address.'
    )]
    public function update(array $fields): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call(
                'crm.address.update',
                [
                    'fields' => $fields,
                ]
            )
        );
    }

    /**
     * Count Addresses by filter
     *
     * @param array{
     *   TYPE_ID?: int,
     *   ENTITY_TYPE_ID?: int,
     *   ENTITY_ID?: int,
     *   ADDRESS_1?: string,
     *   ADDRESS_2?: string,
     *   CITY?: string,
     *   POSTAL_CODE?: string,
     *   REGION?: string,
     *   PROVINCE?: string,
     *   COUNTRY?: string,
     *   COUNTRY_CODE?: string,
     *   LOC_ADDR_ID?: int,
     *   ANCHOR_TYPE_ID?: int,
     *   ANCHOR_ID?: int,
     *   } $filter
     *
     * @return int
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     * @throws \Bitrix24\SDK\Core\Exceptions\TransportException
     */
    public function countByFilter(array $filter = []): int
    {
        return $this->list([], $filter, ['TYPE_ID'], -1)->getCoreResponse()->getResponseData()->getPagination()->getTotal();
    }
}
