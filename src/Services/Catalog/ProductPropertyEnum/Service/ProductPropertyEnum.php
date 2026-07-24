<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <algonexys@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Catalog\ProductPropertyEnum\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Catalog\ProductPropertyEnum\Result\ProductPropertyEnumFieldsResult;
use Bitrix24\SDK\Services\Catalog\ProductPropertyEnum\Result\ProductPropertyEnumResult;
use Bitrix24\SDK\Services\Catalog\ProductPropertyEnum\Result\ProductPropertyEnumsResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['catalog']))]
class ProductPropertyEnum extends AbstractService
{
    public function __construct(
        public Batch    $batch,
        CoreInterface   $core,
        LoggerInterface $logger
    ) {
        parent::__construct($core, $logger);
    }

    /**
     * Adds a new value for a list-type product or variation property.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/product-property-enum/catalog-product-property-enum-add.html
     *
     * @param array{
     *   propertyId: int,
     *   value: string,
     *   xmlId: string,
     *   def?: string,
     *   sort?: int
     * } $fields
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.productPropertyEnum.add',
        'https://apidocs.bitrix24.com/api-reference/catalog/product-property-enum/catalog-product-property-enum-add.html',
        'Adds a new value for a list-type product or variation property'
    )]
    public function add(array $fields): ProductPropertyEnumResult
    {
        return new ProductPropertyEnumResult(
            $this->core->call('catalog.productPropertyEnum.add', ['fields' => $fields])
        );
    }

    /**
     * Updates a list-type property value of a commercial catalog product or variation.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/product-property-enum/catalog-product-property-enum-update.html
     *
     * @param array{
     *   propertyId: int,
     *   value: string,
     *   xmlId: string,
     *   def?: string,
     *   sort?: int
     * } $fields
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.productPropertyEnum.update',
        'https://apidocs.bitrix24.com/api-reference/catalog/product-property-enum/catalog-product-property-enum-update.html',
        'Updates a list-type property value of a commercial catalog product or variation'
    )]
    public function update(int $id, array $fields): ProductPropertyEnumResult
    {
        return new ProductPropertyEnumResult(
            $this->core->call('catalog.productPropertyEnum.update', [
                'id' => $id,
                'fields' => $fields,
            ])
        );
    }

    /**
     * Returns a list-type property value by its identifier.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/product-property-enum/catalog-product-property-enum-get.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.productPropertyEnum.get',
        'https://apidocs.bitrix24.com/api-reference/catalog/product-property-enum/catalog-product-property-enum-get.html',
        'Returns a list-type property value by its identifier'
    )]
    public function get(int $id): ProductPropertyEnumResult
    {
        return new ProductPropertyEnumResult(
            $this->core->call('catalog.productPropertyEnum.get', ['id' => $id])
        );
    }

    /**
     * Returns a list of list-type property values by filter.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/product-property-enum/catalog-product-property-enum-list.html
     *
     * @param string[] $select
     * @param array<string, mixed> $filter
     * @param array<string, string> $order
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.productPropertyEnum.list',
        'https://apidocs.bitrix24.com/api-reference/catalog/product-property-enum/catalog-product-property-enum-list.html',
        'Returns a list of list-type property values by filter'
    )]
    public function list(array $select = [], array $filter = [], array $order = []): ProductPropertyEnumsResult
    {
        $params = [];
        if ($select !== []) {
            $params['select'] = $select;
        }

        if ($filter !== []) {
            $params['filter'] = $filter;
        }

        if ($order !== []) {
            $params['order'] = $order;
        }

        return new ProductPropertyEnumsResult(
            $this->core->call('catalog.productPropertyEnum.list', $params)
        );
    }

    /**
     * Deletes a list-type property value by its identifier.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/product-property-enum/catalog-product-property-enum-delete.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.productPropertyEnum.delete',
        'https://apidocs.bitrix24.com/api-reference/catalog/product-property-enum/catalog-product-property-enum-delete.html',
        'Deletes a list-type property value by its identifier'
    )]
    public function delete(int $id): DeletedItemResult
    {
        return new DeletedItemResult(
            $this->core->call('catalog.productPropertyEnum.delete', ['id' => $id])
        );
    }

    /**
     * Returns the field description of list-type property values.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/product-property-enum/catalog-product-property-enum-get-fields.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.productPropertyEnum.getFields',
        'https://apidocs.bitrix24.com/api-reference/catalog/product-property-enum/catalog-product-property-enum-get-fields.html',
        'Returns the field description of list-type property values'
    )]
    public function getFields(): ProductPropertyEnumFieldsResult
    {
        return new ProductPropertyEnumFieldsResult(
            $this->core->call('catalog.productPropertyEnum.getFields', [])
        );
    }
}
