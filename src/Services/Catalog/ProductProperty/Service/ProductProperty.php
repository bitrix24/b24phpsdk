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

namespace Bitrix24\SDK\Services\Catalog\ProductProperty\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Catalog\ProductProperty\Result\DeletedProductPropertyResult;
use Bitrix24\SDK\Services\Catalog\ProductProperty\Result\ProductPropertiesResult;
use Bitrix24\SDK\Services\Catalog\ProductProperty\Result\ProductPropertyFieldsResult;
use Bitrix24\SDK\Services\Catalog\ProductProperty\Result\ProductPropertyResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['catalog']))]
class ProductProperty extends AbstractService
{
    /**
     * ProductProperty constructor
     */
    public function __construct(public Batch $batch, CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Adds a product or variation property to the commercial catalog
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/product-property/catalog-product-property-add.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.productProperty.add',
        'https://apidocs.bitrix24.com/api-reference/catalog/product-property/catalog-product-property-add.html',
        'Adds a product or variation property to the commercial catalog'
    )]
    public function add(array $fields): ProductPropertyResult
    {
        return new ProductPropertyResult(
            $this->core->call('catalog.productProperty.add', ['fields' => $fields])
        );
    }

    /**
     * Updates fields of a product or variation property in the commercial catalog
     *
     * NOTE: despite the official docs marking `iblockId` as optional in `fields`, the live API
     * requires it — omitting it fails with "Required fields: iblockId". Callers must always pass
     * `iblockId` in $fields.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/product-property/catalog-product-property-update.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.productProperty.update',
        'https://apidocs.bitrix24.com/api-reference/catalog/product-property/catalog-product-property-update.html',
        'Updates fields of a product or variation property in the commercial catalog'
    )]
    public function update(int $id, array $fields): ProductPropertyResult
    {
        return new ProductPropertyResult(
            $this->core->call('catalog.productProperty.update', ['id' => $id, 'fields' => $fields])
        );
    }

    /**
     * Returns the values of the product or variation property fields by its identifier
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/product-property/catalog-product-property-get.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.productProperty.get',
        'https://apidocs.bitrix24.com/api-reference/catalog/product-property/catalog-product-property-get.html',
        'Returns the values of the product or variation property fields by its identifier'
    )]
    public function get(int $id): ProductPropertyResult
    {
        return new ProductPropertyResult($this->core->call('catalog.productProperty.get', ['id' => $id]));
    }

    /**
     * Returns a list of product and variation properties by filter
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/product-property/catalog-product-property-list.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.productProperty.list',
        'https://apidocs.bitrix24.com/api-reference/catalog/product-property/catalog-product-property-list.html',
        'Returns a list of product and variation properties by filter'
    )]
    public function list(array $select = [], array $filter = [], array $order = []): ProductPropertiesResult
    {
        return new ProductPropertiesResult(
            $this->core->call(
                'catalog.productProperty.list',
                [
                    'select' => $select,
                    'filter' => $filter,
                    'order' => $order,
                ]
            )
        );
    }

    /**
     * Removes a product or variation property by its identifier
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/product-property/catalog-product-property-delete.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.productProperty.delete',
        'https://apidocs.bitrix24.com/api-reference/catalog/product-property/catalog-product-property-delete.html',
        'Removes a product or variation property by its identifier'
    )]
    public function delete(int $id): DeletedProductPropertyResult
    {
        return new DeletedProductPropertyResult(
            $this->core->call('catalog.productProperty.delete', ['id' => $id])
        );
    }

    /**
     * Returns the description of product or variation property fields
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/product-property/catalog-product-property-get-fields.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.productProperty.getFields',
        'https://apidocs.bitrix24.com/api-reference/catalog/product-property/catalog-product-property-get-fields.html',
        'Returns the description of product or variation property fields'
    )]
    public function getFields(): ProductPropertyFieldsResult
    {
        return new ProductPropertyFieldsResult($this->core->call('catalog.productProperty.getFields'));
    }
}
