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

namespace Bitrix24\SDK\Services\Catalog\StoreProduct\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Catalog\StoreProduct\Result\StoreProductFieldsResult;
use Bitrix24\SDK\Services\Catalog\StoreProduct\Result\StoreProductResult;
use Bitrix24\SDK\Services\Catalog\StoreProduct\Result\StoreProductsResult;

#[ApiServiceMetadata(new Scope(['catalog']))]
class StoreProduct extends AbstractService
{
    /**
     * Returns information about product stock by record identifier.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/store-product/catalog-store-product-get.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.storeproduct.get',
        'https://apidocs.bitrix24.com/api-reference/catalog/store-product/catalog-store-product-get.html',
        'Returns information about product stock by record identifier.'
    )]
    public function get(int $id): StoreProductResult
    {
        $this->guardPositiveId($id);

        return new StoreProductResult($this->core->call('catalog.storeproduct.get', ['id' => $id]));
    }

    /**
     * Returns a list of product stock records by filter.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/store-product/catalog-store-product-list.html
     *
     * @param string[]               $select
     * @param array<string, mixed>   $filter
     * @param array<string, string>  $order
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.storeproduct.list',
        'https://apidocs.bitrix24.com/api-reference/catalog/store-product/catalog-store-product-list.html',
        'Returns a list of product stock records by filter.'
    )]
    public function list(array $select = [], array $filter = [], array $order = []): StoreProductsResult
    {
        return new StoreProductsResult($this->core->call('catalog.storeproduct.list', [
            'select' => $select,
            'filter' => $filter,
            'order' => $order,
        ]));
    }

    /**
     * Returns the fields of product stock records.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/store-product/catalog-store-product-get-fields.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.storeproduct.getFields',
        'https://apidocs.bitrix24.com/api-reference/catalog/store-product/catalog-store-product-get-fields.html',
        'Returns the fields of product stock records.'
    )]
    public function getFields(): StoreProductFieldsResult
    {
        return new StoreProductFieldsResult($this->core->call('catalog.storeproduct.getFields'));
    }
}
