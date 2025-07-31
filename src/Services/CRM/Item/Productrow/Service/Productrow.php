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

namespace Bitrix24\SDK\Services\CRM\Item\Productrow\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
//use Bitrix24\SDK\Core\Result\FieldsResult;
//use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\CRM\Item\Productrow\Result\ProductrowResult;
use Bitrix24\SDK\Services\CRM\Item\Productrow\Result\ProductrowFieldsResult;
use Bitrix24\SDK\Services\CRM\Item\Productrow\Result\ProductrowsResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['crm']))]
class Productrow extends AbstractService
{
    public function __construct(public Batch $batch, CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Adds a product item.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/universal/product-rows/crm-item-productrow-add.html
     *
     * @param array{
     *   id?: int,
     *   ownerId?: int,
     *   ownerType?: string,
     *   productId?: int,
     *   productName?: string,
     *   price?: string,
     *   quantity?: string,
     *   discountTypeId?: int,
     *   discountRate?: string,
     *   discountSum?: string,
     *   taxRate?: string,
     *   taxIncluded?: bool,
     *   customized?: bool,
     *   measureCode?: int,
     *   measureName?: string,
     *   sort?: int,
     * } $fields
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.item.productrow.add',
        'https://apidocs.bitrix24.com/api-reference/crm/universal/product-rows/crm-item-productrow-add.html',
        'Adds a product item.'
    )]
    public function add(array $fields): ProductrowResult
    {
        return new ProductrowResult(
            $this->core->call(
                'crm.item.productrow.add',
                [
                    'fields' => $fields,
                ]
            )
        );
    }

    /**
     * Deletes a product item.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/universal/product-rows/crm-item-productrow-delete.html
     *
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.item.productrow.delete',
        'https://apidocs.bitrix24.com/api-reference/crm/universal/product-rows/crm-item-productrow-delete.html',
        'Deletes a product item.'
    )]
    public function delete(int $id): DeletedItemResult
    {
        return new DeletedItemResult(
            $this->core->call(
                'crm.item.productrow.delete',
                ['id' => $id]
            )
        );
    }

    /**
     * Retrieves a list of product item fields.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/universal/product-rows/crm-item-productrow-fields.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.item.productrow.fields',
        'https://apidocs.bitrix24.com/api-reference/crm/universal/product-rows/crm-item-productrow-fields.html',
        'Retrieves a list of product item fields.'
    )]
    public function fields(): ProductrowFieldsResult
    {
        return new ProductrowFieldsResult($this->core->call('crm.item.productrow.fields', []));
    }

    /**
     * Retrieves information about a product item by id.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/universal/product-rows/crm-item-productrow-get.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.item.productrow.get',
        'https://apidocs.bitrix24.com/api-reference/crm/universal/product-rows/crm-item-productrow-get.html',
        'Retrieves information about a product item by id.'
    )]
    public function get(int $id): ProductrowResult
    {
        return new ProductrowResult($this->core->call('crm.item.productrow.get', ['id' => $id]));
    }

    /**
     * Retrieves a list of product items
     *
     * The following keys must be present in the filter: ownerType, ownerId
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/universal/product-rows/crm-item-productrow-list.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.item.productrow.list',
        'https://apidocs.bitrix24.com/api-reference/crm/universal/product-rows/crm-item-productrow-list.html',
        'Retrieves a list of product items'
    )]
    public function list(array $order, array $filter, int $startItem = 0): ProductrowsResult
    {
        return new ProductrowsResult(
            $this->core->call(
                'crm.item.productrow.list',
                [
                    'order' => $order,
                    'filter' => $filter,
                    'start' => $startItem,
                ]
            )
        );
    }

    /**
     * Updates a product item.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/universal/product-rows/crm-item-productrow-update.html
     *
     * @param array{
     *   id?: int,
     *   ownerId?: int,
     *   ownerType?: string,
     *   productId?: int,
     *   productName?: string,
     *   price?: string,
     *   quantity?: string,
     *   discountTypeId?: int,
     *   discountRate?: string,
     *   discountSum?: string,
     *   taxRate?: string,
     *   taxIncluded?: bool,
     *   customized?: bool,
     *   measureCode?: int,
     *   measureName?: string,
     *   sort?: int,
     * } $fields
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.item.productrow.update',
        'https://apidocs.bitrix24.com/api-reference/crm/universal/product-rows/crm-item-productrow-update.html',
        'Updates a product item.'
    )]
    public function update(int $id, array $fields): ProductrowResult
    {
        return new ProductrowResult(
            $this->core->call(
                'crm.item.productrow.update',
                [
                    'id' => $id,
                    'fields' => $fields,
                ]
            )
        );
    }

    /**
     * Associates a product item with a CRM object.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/universal/product-rows/crm-item-productrow-set.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.item.productrow.set',
        'https://apidocs.bitrix24.com/api-reference/crm/universal/product-rows/crm-item-productrow-set.html',
        'Associates a product item with a CRM object.'
    )]
    public function set(int $ownerId, string $ownerType, array $productRows): ProductrowsResult
    {
        return new ProductrowsResult(
            $this->core->call(
                'crm.item.productrow.set',
                [
                    'ownerId' => $ownerId,
                    'ownerType' => $ownerType,
                    'productRows' => $productRows,
                ]
            )
        );
    }

    /**
     * Retrieves a list of unpaid products.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/universal/product-rows/crm-item-productrow-get-available-for-payment.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.item.productrow.getAvailableForPayment',
        'https://apidocs.bitrix24.com/api-reference/crm/universal/product-rows/crm-item-productrow-get-available-for-payment.html',
        'Retrieves a list of unpaid products.'
    )]
    public function getAvailableForPayment(int $ownerId, string $ownerType): ProductrowsResult
    {
        return new ProductrowsResult(
            $this->core->call(
                'crm.item.productrow.getAvailableForPayment',
                [
                    'ownerId' => $ownerId,
                    'ownerType' => $ownerType,
                ]
            )
        );
    }

    /**
     * Count by filter
     *
     * The following keys must be present in the filter: ownerType, ownerId
     *
     * @param array{
     *   id?: int,
     *   ownerId?: int,
     *   ownerType?: string,
     *   productId?: int,
     *   productName?: string,
     *   price?: string,
     *   priceExclusive?: string,
     *   priceNetto?: string,
     *   priceBrutto?: string,
     *   quantity?: string,
     *   discountTypeId?: int,
     *   discountRate?: string,
     *   discountSum?: string,
     *   taxRate?: string,
     *   taxIncluded?: bool,
     *   customized?: bool,
     *   measureCode?: int,
     *   measureName?: string,
     *   sort?: int,
     *   type?: int,
     *   storeId?: int,
     * } $filter
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function countByFilter($filter = []): int
    {
        return $this->list([], $filter, 1)->getCoreResponse()->getResponseData()->getPagination()->getTotal();
    }
}
