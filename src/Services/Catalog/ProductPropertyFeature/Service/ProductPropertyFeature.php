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

namespace Bitrix24\SDK\Services\Catalog\ProductPropertyFeature\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Catalog\ProductPropertyFeature\Result\AvailableFeaturesResult;
use Bitrix24\SDK\Services\Catalog\ProductPropertyFeature\Result\ProductPropertyFeatureAddedResult;
use Bitrix24\SDK\Services\Catalog\ProductPropertyFeature\Result\ProductPropertyFeatureFieldsResult;
use Bitrix24\SDK\Services\Catalog\ProductPropertyFeature\Result\ProductPropertyFeatureResult;
use Bitrix24\SDK\Services\Catalog\ProductPropertyFeature\Result\ProductPropertyFeatureUpdatedResult;
use Bitrix24\SDK\Services\Catalog\ProductPropertyFeature\Result\ProductPropertyFeaturesResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['catalog']))]
class ProductPropertyFeature extends AbstractService
{
    public function __construct(CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Adds a parameter (feature) for a product or variation property.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/product-property-feature/catalog-product-property-feature-add.html
     *
     * @param array{
     *   propertyId: int,
     *   moduleId: string,
     *   featureId: string,
     *   isEnabled: string,
     * } $fields
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.productPropertyFeature.add',
        'https://apidocs.bitrix24.com/api-reference/catalog/product-property-feature/catalog-product-property-feature-add.html',
        'Adds a parameter of a product or variation property'
    )]
    public function add(array $fields): ProductPropertyFeatureAddedResult
    {
        return new ProductPropertyFeatureAddedResult(
            $this->core->call('catalog.productPropertyFeature.add', [
                'fields' => $fields,
            ])
        );
    }

    /**
     * Updates a parameter of a product or variation property by id.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/product-property-feature/catalog-product-property-feature-update.html
     *
     * @param array{
     *   propertyId: int,
     *   moduleId: string,
     *   featureId: string,
     *   isEnabled: string,
     * } $fields
     *
     * @throws BaseException
     * @throws InvalidArgumentException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.productPropertyFeature.update',
        'https://apidocs.bitrix24.com/api-reference/catalog/product-property-feature/catalog-product-property-feature-update.html',
        'Updates a parameter of a product or variation property'
    )]
    public function update(int $id, array $fields): ProductPropertyFeatureUpdatedResult
    {
        $this->guardPositiveId($id);

        return new ProductPropertyFeatureUpdatedResult(
            $this->core->call('catalog.productPropertyFeature.update', [
                'id' => $id,
                'fields' => $fields,
            ])
        );
    }

    /**
     * Returns a product or variation property parameter by id.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/product-property-feature/catalog-product-property-feature-get.html
     *
     * @throws BaseException
     * @throws InvalidArgumentException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.productPropertyFeature.get',
        'https://apidocs.bitrix24.com/api-reference/catalog/product-property-feature/catalog-product-property-feature-get.html',
        'Returns a product or variation property parameter by id'
    )]
    public function get(int $id): ProductPropertyFeatureResult
    {
        $this->guardPositiveId($id);

        return new ProductPropertyFeatureResult(
            $this->core->call('catalog.productPropertyFeature.get', [
                'id' => $id,
            ])
        );
    }

    /**
     * Returns the list of product/variation property parameters matching the filter.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/product-property-feature/catalog-product-property-feature-list.html
     *
     * @param array<int, string>                                $select Fields to select
     * @param array<string, scalar|array{0?: scalar, 1?:scalar}> $filter Filter map
     * @param array<string, 'asc'|'desc'|'ASC'|'DESC'>           $order  Sort order map
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.productPropertyFeature.list',
        'https://apidocs.bitrix24.com/api-reference/catalog/product-property-feature/catalog-product-property-feature-list.html',
        'Returns the list of product/variation property parameters'
    )]
    public function list(array $select = [], array $filter = [], array $order = []): ProductPropertyFeaturesResult
    {
        return new ProductPropertyFeaturesResult(
            $this->core->call('catalog.productPropertyFeature.list', [
                'select' => $select,
                'filter' => $filter,
                'order' => $order,
            ])
        );
    }

    /**
     * Returns the list of available parameters (features) for the given product or variation property.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/product-property-feature/catalog-product-property-feature-get-available-features-by-property.html
     *
     * @throws BaseException
     * @throws InvalidArgumentException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.productPropertyFeature.getAvailableFeaturesByProperty',
        'https://apidocs.bitrix24.com/api-reference/catalog/product-property-feature/catalog-product-property-feature-get-available-features-by-property.html',
        'Returns the list of available parameters for the given product or variation property'
    )]
    public function getAvailableFeaturesByProperty(int $propertyId): AvailableFeaturesResult
    {
        $this->guardPositiveId($propertyId);

        return new AvailableFeaturesResult(
            $this->core->call('catalog.productPropertyFeature.getAvailableFeaturesByProperty', [
                'propertyId' => $propertyId,
            ])
        );
    }

    /**
     * Returns the description of product/variation property parameter fields.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/product-property-feature/catalog-product-property-feature-get-fields.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.productPropertyFeature.getFields',
        'https://apidocs.bitrix24.com/api-reference/catalog/product-property-feature/catalog-product-property-feature-get-fields.html',
        'Returns the description of product/variation property parameter fields'
    )]
    public function getFields(): ProductPropertyFeatureFieldsResult
    {
        return new ProductPropertyFeatureFieldsResult(
            $this->core->call('catalog.productPropertyFeature.getFields')
        );
    }
}
