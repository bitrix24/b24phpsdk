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

namespace Bitrix24\SDK\Services\Catalog\Product\ProductService\Service;

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
use Bitrix24\SDK\Services\Catalog\Product\ProductService\Result\ProductServiceResult;
use Bitrix24\SDK\Services\Catalog\Product\ProductService\Result\ProductServicesResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['catalog']))]
class ProductService extends AbstractService
{
    public function __construct(CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * The method adds a service to the commercial catalog.
     *
     * @see https://apidocs.bitrix24.com/api-reference/catalog/product/service/catalog-product-service-add.html
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.product.service.add',
        'https://apidocs.bitrix24.com/api-reference/catalog/product/service/catalog-product-service-add.html',
        'The method adds a service to the commercial catalog.'
    )]
    public function add(array $fields): ProductServiceResult
    {
        return new ProductServiceResult($this->core->call('catalog.product.service.add', ['fields' => $fields]));
    }

    /**
     * The method updates a service in the commercial catalog by its identifier.
     *
     * @see https://apidocs.bitrix24.com/api-reference/catalog/product/service/catalog-product-service-update.html
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.product.service.update',
        'https://apidocs.bitrix24.com/api-reference/catalog/product/service/catalog-product-service-update.html',
        'The method updates a service in the commercial catalog by its identifier.'
    )]
    public function update(int $serviceId, array $fields): ProductServiceResult
    {
        return new ProductServiceResult($this->core->call('catalog.product.service.update', [
            'id' => $serviceId,
            'fields' => $fields,
        ]));
    }

    /**
     * The method gets field values of a commercial catalog service by ID.
     *
     * @see https://apidocs.bitrix24.com/api-reference/catalog/product/service/catalog-product-service-get.html
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.product.service.get',
        'https://apidocs.bitrix24.com/api-reference/catalog/product/service/catalog-product-service-get.html',
        'The method gets field values of a commercial catalog service by ID.'
    )]
    public function get(int $serviceId): ProductServiceResult
    {
        return new ProductServiceResult($this->core->call('catalog.product.service.get', ['id' => $serviceId]));
    }

    /**
     * The method gets a list of commercial catalog services by filter.
     *
     * @see https://apidocs.bitrix24.com/api-reference/catalog/product/service/catalog-product-service-list.html
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.product.service.list',
        'https://apidocs.bitrix24.com/api-reference/catalog/product/service/catalog-product-service-list.html',
        'The method gets a list of commercial catalog services by filter.'
    )]
    public function list(array $select, array $filter, array $order = []): ProductServicesResult
    {
        return new ProductServicesResult($this->core->call('catalog.product.service.list', [
            'select' => $select,
            'filter' => $filter,
            'order' => $order,
        ]));
    }

    /**
     * The method deletes a commercial catalog service by ID.
     *
     * @see https://apidocs.bitrix24.com/api-reference/catalog/product/service/catalog-product-service-delete.html
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.product.service.delete',
        'https://apidocs.bitrix24.com/api-reference/catalog/product/service/catalog-product-service-delete.html',
        'The method deletes a commercial catalog service by ID.'
    )]
    public function delete(int $serviceId): DeletedItemResult
    {
        return new DeletedItemResult($this->core->call('catalog.product.service.delete', ['id' => $serviceId]));
    }

    /**
     * The method returns commercial catalog service fields by filter.
     *
     * @see https://apidocs.bitrix24.com/api-reference/catalog/product/service/catalog-product-service-get-fields-by-filter.html
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.product.service.getFieldsByFilter',
        'https://apidocs.bitrix24.com/api-reference/catalog/product/service/catalog-product-service-get-fields-by-filter.html',
        'The method returns commercial catalog service fields by filter.'
    )]
    public function fieldsByFilter(int $iblockId): FieldsResult
    {
        return new FieldsResult($this->core->call('catalog.product.service.getFieldsByFilter', [
            'filter' => ['iblockId' => $iblockId],
        ]));
    }

    /**
     * The method downloads commercial catalog service files by the given parameters.
     *
     * @see https://apidocs.bitrix24.com/api-reference/catalog/product/service/catalog-product-service-download.html
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.product.service.download',
        'https://apidocs.bitrix24.com/api-reference/catalog/product/service/catalog-product-service-download.html',
        'The method downloads commercial catalog service files by the given parameters.'
    )]
    public function download(int $fileId, int $productId, string $fieldName): Response
    {
        return $this->core->call('catalog.product.service.download', [
            'fields' => [
                'fileId' => $fileId,
                'productId' => $productId,
                'fieldName' => $fieldName,
            ],
        ]);
    }
}
