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

namespace Bitrix24\SDK\Services\Catalog\Product\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Core\Result\FieldsResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Catalog\Common\ProductType;
use Bitrix24\SDK\Services\Catalog\Product\Result\ProductResult;
use Bitrix24\SDK\Services\Catalog\Product\Result\ProductsResult;

use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['catalog']))]
class Product extends AbstractService
{
    public function __construct(
        public Batch           $batch,
        CoreInterface   $core,
        LoggerInterface $logger
    )
    {
        parent::__construct($core, $logger);
    }

    /**
     * The method gets field value of commercial catalog product by ID.
     *
     * @see https://apidocs.bitrix24.com/api-reference/catalog/product/catalog-product-get.html
     * @throws TransportException
     * @throws BaseException
     */
    #[ApiEndpointMetadata(
        'catalog.product.get',
        'https://apidocs.bitrix24.com/api-reference/catalog/product/catalog-product-get.html',
        'The method gets field value of commercial catalog product by ID.'
    )]
    public function get(int $productId): ProductResult
    {
        return new ProductResult($this->core->call('catalog.product.get', ['id' => $productId]));
    }

    /**
     * The method adds a commercial catalog product.
     *
     * @see https://apidocs.bitrix24.com/api-reference/catalog/product/catalog-product-add.html
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.product.add',
        'https://apidocs.bitrix24.com/api-reference/catalog/product/catalog-product-add.html',
        'The method adds a commercial catalog product.'
    )]
    public function add(array $productFields): ProductResult
    {
        return new ProductResult($this->core->call('catalog.product.add', [
                'fields' => $productFields
            ]
        ));
    }

    /**
     * The method deletes commercial catalog product.
     *
     * @see https://apidocs.bitrix24.com/api-reference/catalog/product/catalog-product-delete.html
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.product.delete',
        'https://apidocs.bitrix24.com/api-reference/catalog/product/catalog-product-delete.html',
        'The method deletes commercial catalog product by ID'
    )]
    public function delete(int $productId): DeletedItemResult
    {
        return new DeletedItemResult($this->core->call('catalog.product.delete', ['id' => $productId]));
    }

    /**
     * The method gets list of commercial catalog products by filter.
     *
     * @see https://apidocs.bitrix24.com/api-reference/catalog/product/catalog-product-list.html
     * @throws TransportException
     * @throws BaseException
     */
    #[ApiEndpointMetadata(
        'catalog.product.list',
        'https://apidocs.bitrix24.com/api-reference/catalog/product/catalog-product-list.html',
        'The method gets list of commercial catalog products by filter.'
    )]
    public function list(array $select, array $filter, array $order, int $start): ProductsResult
    {
        return new ProductsResult($this->core->call('catalog.product.list', [
            'select' => $select,
            'filter' => $filter,
            'order' => $order,
            'start' => $start
        ]));
    }

    /**
     * The method returns commercial catalog product fields by filter.
     * @see https://apidocs.bitrix24.com/api-reference/catalog/product/catalog-product-get-fields-by-filter.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.product.getFieldsByFilter',
        'https://apidocs.bitrix24.com/api-reference/catalog/product/catalog-product-get-fields-by-filter.html',
        'The method returns commercial catalog product fields by filter.'
    )]
    public function fieldsByFilter(int $iblockId, ProductType $productType, ?array $additionalFilter = null): FieldsResult
    {
        $filter = [
            'iblockId' => $iblockId,
            'productType' => $productType->value
        ];
        if ($additionalFilter !== null) {
            $filter = array_merge($filter, $additionalFilter);
        }

        return new FieldsResult($this->core->call('catalog.product.getFieldsByFilter', ['filter' => $filter]));
    }

    /**
     * The method updates commercial catalog product by ID with the given fields.
     *
     * @see https://apidocs.bitrix24.com/api-reference/catalog/product/catalog-product-update.html
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.product.update',
        'https://apidocs.bitrix24.com/api-reference/catalog/product/catalog-product-update.html',
        'The method updates commercial catalog product by ID with the given fields.'
    )]
    public function update(int $productId, array $productFields): ProductResult
    {
        return new ProductResult($this->core->call('catalog.product.update', [
            'id' => $productId,
            'fields' => $productFields,
        ]));
    }

    /**
     * The method downloads commercial catalog product files by the given parameters.
     *
     * @see https://apidocs.bitrix24.com/api-reference/catalog/product/catalog-product-download.html
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.product.download',
        'https://apidocs.bitrix24.com/api-reference/catalog/product/catalog-product-download.html',
        'The method downloads commercial catalog product files by the given parameters.'
    )]
    public function download(int $fileId, int $productId, string $fieldName): Response
    {
        return $this->core->call('catalog.product.download', [
            'fields' => [
                'fileId' => $fileId,
                'productId' => $productId,
                'fieldName' => $fieldName,
            ],
        ]);
    }
}