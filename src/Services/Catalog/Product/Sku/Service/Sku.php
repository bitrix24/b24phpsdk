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

namespace Bitrix24\SDK\Services\Catalog\Product\Sku\Service;

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
use Bitrix24\SDK\Services\Catalog\Product\Sku\Result\SkuResult;
use Bitrix24\SDK\Services\Catalog\Product\Sku\Result\SkusResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['catalog']))]
class Sku extends AbstractService
{
    public function __construct(CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * The method adds a parent (SKU) product to the commercial catalog.
     *
     * @see https://apidocs.bitrix24.com/api-reference/catalog/product/sku/catalog-product-sku-add.html
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.product.sku.add',
        'https://apidocs.bitrix24.com/api-reference/catalog/product/sku/catalog-product-sku-add.html',
        'The method adds a parent (SKU) product to the commercial catalog.'
    )]
    public function add(array $fields): SkuResult
    {
        return new SkuResult($this->core->call('catalog.product.sku.add', ['fields' => $fields]));
    }

    /**
     * The method updates a parent (SKU) product in the commercial catalog by its identifier.
     *
     * @see https://apidocs.bitrix24.com/api-reference/catalog/product/sku/catalog-product-sku-update.html
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.product.sku.update',
        'https://apidocs.bitrix24.com/api-reference/catalog/product/sku/catalog-product-sku-update.html',
        'The method updates a parent (SKU) product in the commercial catalog by its identifier.'
    )]
    public function update(int $skuId, array $fields): SkuResult
    {
        return new SkuResult($this->core->call('catalog.product.sku.update', [
            'id' => $skuId,
            'fields' => $fields,
        ]));
    }

    /**
     * The method gets field values of a parent (SKU) product by ID.
     *
     * @see https://apidocs.bitrix24.com/api-reference/catalog/product/sku/catalog-product-sku-get.html
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.product.sku.get',
        'https://apidocs.bitrix24.com/api-reference/catalog/product/sku/catalog-product-sku-get.html',
        'The method gets field values of a parent (SKU) product by ID.'
    )]
    public function get(int $skuId): SkuResult
    {
        return new SkuResult($this->core->call('catalog.product.sku.get', ['id' => $skuId]));
    }

    /**
     * The method gets a list of parent (SKU) products by filter.
     *
     * @see https://apidocs.bitrix24.com/api-reference/catalog/product/sku/catalog-product-sku-list.html
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.product.sku.list',
        'https://apidocs.bitrix24.com/api-reference/catalog/product/sku/catalog-product-sku-list.html',
        'The method gets a list of parent (SKU) products by filter.'
    )]
    public function list(array $select, array $filter, array $order = []): SkusResult
    {
        return new SkusResult($this->core->call('catalog.product.sku.list', [
            'select' => $select,
            'filter' => $filter,
            'order' => $order,
        ]));
    }

    /**
     * The method deletes a parent (SKU) product by ID.
     *
     * @see https://apidocs.bitrix24.com/api-reference/catalog/product/sku/catalog-product-sku-delete.html
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.product.sku.delete',
        'https://apidocs.bitrix24.com/api-reference/catalog/product/sku/catalog-product-sku-delete.html',
        'The method deletes a parent (SKU) product by ID.'
    )]
    public function delete(int $skuId): DeletedItemResult
    {
        return new DeletedItemResult($this->core->call('catalog.product.sku.delete', ['id' => $skuId]));
    }

    /**
     * The method returns parent (SKU) product fields by filter.
     *
     * @see https://apidocs.bitrix24.com/api-reference/catalog/product/sku/catalog-product-sku-get-fields-by-filter.html
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.product.sku.getFieldsByFilter',
        'https://apidocs.bitrix24.com/api-reference/catalog/product/sku/catalog-product-sku-get-fields-by-filter.html',
        'The method returns parent (SKU) product fields by filter.'
    )]
    public function fieldsByFilter(int $iblockId): FieldsResult
    {
        return new FieldsResult($this->core->call('catalog.product.sku.getFieldsByFilter', [
            'filter' => ['iblockId' => $iblockId],
        ]));
    }

    /**
     * The method downloads parent (SKU) product files by the given parameters.
     *
     * @see https://apidocs.bitrix24.com/api-reference/catalog/product/sku/catalog-product-sku-download.html
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.product.sku.download',
        'https://apidocs.bitrix24.com/api-reference/catalog/product/sku/catalog-product-sku-download.html',
        'The method downloads parent (SKU) product files by the given parameters.'
    )]
    public function download(int $fileId, int $productId, string $fieldName): Response
    {
        return $this->core->call('catalog.product.sku.download', [
            'fields' => [
                'fileId' => $fileId,
                'productId' => $productId,
                'fieldName' => $fieldName,
            ],
        ]);
    }
}
