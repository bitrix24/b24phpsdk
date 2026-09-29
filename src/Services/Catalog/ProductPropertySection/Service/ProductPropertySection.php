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

namespace Bitrix24\SDK\Services\Catalog\ProductPropertySection\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Catalog\ProductPropertySection\Result\ProductPropertySectionResult;
use Bitrix24\SDK\Services\Catalog\ProductPropertySection\Result\ProductPropertySectionsResult;

#[ApiServiceMetadata(new Scope(['catalog']))]
class ProductPropertySection extends AbstractService
{
    /**
     * Returns the section settings of a product property or variation by the property ID.
     *
     * @see https://apidocs.bitrix24.com/api-reference/catalog/product-property-section/catalog-product-property-section-get.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.productPropertySection.get',
        'https://apidocs.bitrix24.com/api-reference/catalog/product-property-section/catalog-product-property-section-get.html',
        'Returns the section settings of a product property or variation by the property ID.'
    )]
    public function get(int $propertyId): ProductPropertySectionResult
    {
        return new ProductPropertySectionResult(
            $this->core->call('catalog.productPropertySection.get', ['propertyId' => $propertyId])
        );
    }

    /**
     * Returns a list of section settings for product properties and variations based on a filter.
     *
     * @see https://apidocs.bitrix24.com/api-reference/catalog/product-property-section/catalog-product-property-section-list.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.productPropertySection.list',
        'https://apidocs.bitrix24.com/api-reference/catalog/product-property-section/catalog-product-property-section-list.html',
        'Returns a list of section settings for product properties and variations based on a filter.'
    )]
    public function list(array $select = [], array $filter = [], array $order = []): ProductPropertySectionsResult
    {
        return new ProductPropertySectionsResult(
            $this->core->call('catalog.productPropertySection.list', [
                'select' => $select,
                'filter' => $filter,
                'order' => $order,
            ])
        );
    }

    /**
     * Sets or updates the section settings of a product property or variation.
     *
     * @see https://apidocs.bitrix24.com/api-reference/catalog/product-property-section/catalog-product-property-section-set.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.productPropertySection.set',
        'https://apidocs.bitrix24.com/api-reference/catalog/product-property-section/catalog-product-property-section-set.html',
        'Sets or updates the section settings of a product property or variation.'
    )]
    public function set(int $propertyId, array $fields): ProductPropertySectionResult
    {
        return new ProductPropertySectionResult(
            $this->core->call('catalog.productPropertySection.set', [
                'propertyId' => $propertyId,
                'fields' => $fields,
            ])
        );
    }
}
