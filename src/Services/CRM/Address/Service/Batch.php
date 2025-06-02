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

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AddedItemBatchResult;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Services\CRM\Address\Result\AddressItemResult;
use Bitrix24\SDK\Core\Result\UpdatedItemBatchResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['crm']))]
class Batch
{
    /**
     * Batch constructor.
     */
    public function __construct(protected BatchOperationsInterface $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch list method for Addresses
     *
     * @param array{
     *                          TYPE_ID?: int,
     *                          ENTITY_TYPE_ID?: int,
     *                          ENTITY_ID?: int,
     *                          ADDRESS_1?: string,
     *                          ADDRESS_2?: string,
     *                          CITY?: string,
     *                          POSTAL_CODE?: string,
     *                          REGION?: string,
     *                          PROVINCE?: string,
     *                          COUNTRY?: string,
     *                          COUNTRY_CODE?: string,
     *                          LOC_ADDR_ID?: int,
     *                          ANCHOR_TYPE_ID?: int,
     *                          ANCHOR_ID?: int,
     *                         } $order
     *
     * @param array{
     *                          TYPE_ID?: int,
     *                          ENTITY_TYPE_ID?: int,
     *                          ENTITY_ID?: int,
     *                          ADDRESS_1?: string,
     *                          ADDRESS_2?: string,
     *                          CITY?: string,
     *                          POSTAL_CODE?: string,
     *                          REGION?: string,
     *                          PROVINCE?: string,
     *                          COUNTRY?: string,
     *                          COUNTRY_CODE?: string,
     *                          LOC_ADDR_ID?: int,
     *                          ANCHOR_TYPE_ID?: int,
     *                          ANCHOR_ID?: int,
     *                         } $filter
     * @param array    $select = ['TYPE_ID','ENTITY_TYPE_ID','ENTITY_ID','ADDRESS_1','ADDRESS_2','CITY','POSTAL_CODE','REGION','PROVINCE','COUNTRY','COUNTRY_CODE','LOC_ADDR_ID','ANCHOR_TYPE_ID','ANCHOR_ID']
     *
     * @return Generator<int, AddressItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'crm.address.list',
        'https://apidocs.bitrix24.com/api-reference/crm/requisites/addresses/crm-address-list.html',
        'Batch list method for addresses'
    )]
    public function list(array $order, array $filter, array $select, ?int $limit = null): Generator
    {
        $this->log->debug(
            'batchList',
            [
                'order'  => $order,
                'filter' => $filter,
                'select' => $select,
                'limit'  => $limit,
            ]
        );
        foreach ($this->batch->getTraversableList('crm.address.list', $order, $filter, $select, $limit) as $key => $value) {
            yield $key => new AddressItemResult($value);
        }
    }

    /**
     * Batch adding addresses
     *
     * @param array <int, array{
     *                          TYPE_ID?: int,
     *                          ENTITY_TYPE_ID?: int,
     *                          ENTITY_ID?: int,
     *                          ADDRESS_1?: string,
     *                          ADDRESS_2?: string,
     *                          CITY?: string,
     *                          POSTAL_CODE?: string,
     *                          REGION?: string,
     *                          PROVINCE?: string,
     *                          COUNTRY?: string,
     *                          COUNTRY_CODE?: string,
     *                          LOC_ADDR_ID?: int,
     *   }> $addresses
     *
     * @return Generator<int, AddedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'crm.address.add',
        'https://apidocs.bitrix24.com/api-reference/crm/requisites/addresses/crm-address-add.html',
        'Batch adding addresses'
    )]
    public function add(array $addresses): Generator
    {
        $items = [];
        foreach ($addresses as $address) {
            $items[] = [
                'fields' => $address,
            ];
        }

        foreach ($this->batch->addEntityItems('crm.address.add', $items) as $key => $item) {
            yield $key => new AddedItemBatchResult($item);
        }
    }

    /**
     * Batch delete addresses
     *
     * @param array <int, array{
     *                          TYPE_ID?: int,
     *                          ENTITY_TYPE_ID?: int,
     *                          ENTITY_ID?: int,
     *   }> $addressKeys
     *
     * @return Generator<int, DeletedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'crm.address.delete',
        'https://apidocs.bitrix24.com/api-reference/crm/requisites/addresses/crm-address-delete.html',
        'Batch delete addresses'
    )]
    public function delete(array $addressKeys): Generator
    {
        foreach ($this->batch->deleteEntityItems('crm.address.delete', $addressKeys) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }

    /**
     * Batch update addresses
     *
     * @param array <int, array{
     *                      fields: array{
     *                          TYPE_ID?: int,
     *                          ENTITY_TYPE_ID?: int,
     *                          ENTITY_ID?: int,
     *                          ADDRESS_1?: string,
     *                          ADDRESS_2?: string,
     *                          CITY?: string,
     *                          POSTAL_CODE?: string,
     *                          REGION?: string,
     *                          PROVINCE?: string,
     *                          COUNTRY?: string,
     *                          COUNTRY_CODE?: string,
     *                          LOC_ADDR_ID?: int,
     *                      }
     *   }> $fields
     *
     * @return Generator<int, UpdatedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'crm.address.update',
        'https://apidocs.bitrix24.com/api-reference/crm/requisites/addresses/crm-address-update.html',
        'Batch update addresses'
    )]
    public function update(array $fields): Generator
    {
        foreach ($this->batch->updateEntityItems('crm.address.update', $fields) as $key => $item) {
            yield $key => new UpdatedItemBatchResult($item);
        }
    }

}
