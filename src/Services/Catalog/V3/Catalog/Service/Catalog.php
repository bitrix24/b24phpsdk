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

namespace Bitrix24\SDK\Services\Catalog\V3\Catalog\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Contracts\ItemBuilderInterface;
use Bitrix24\SDK\Core\Contracts\SelectBuilderInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Filters\FilterBuilderInterface;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Catalog\V3\Catalog\Result\DeletedCatalogResult;
use Bitrix24\SDK\Services\Catalog\V3\Catalog\Result\IsOffersCatalogResult;
use Bitrix24\SDK\Services\Catalog\V3\Catalog\Result\CatalogResult;
use Bitrix24\SDK\Services\Catalog\V3\Catalog\Result\CatalogsResult;

#[ApiServiceMetadata(new Scope(['catalog']))]
class Catalog extends AbstractService
{
    /**
     * Returns a trade catalog by ID.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/catalog/catalog-catalog-get-rest-v3.html
     *
     * @param array<int, string>|CatalogSelectBuilder $select
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.catalog.get',
        'https://apidocs.bitrix24.com/api-reference/catalog/catalog/catalog-catalog-get-rest-v3.html',
        'Returns a trade catalog by ID',
        ApiVersion::v3
    )]
    public function get(int $id, array|CatalogSelectBuilder $select = []): CatalogResult
    {
        if ($select instanceof SelectBuilderInterface) {
            $select = $select->buildSelect();
        }

        return new CatalogResult(
            $this->core->call('catalog.catalog.get', $this->withoutEmpty(['id' => $id, 'select' => $select]), ApiVersion::v3)
        );
    }

    /**
     * Returns a list of trade catalogs.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/catalog/catalog-catalog-list-rest-v3.html
     *
     * @param array<int, mixed>|FilterBuilderInterface $filter Conditions such as [['productIblockId', '=', 0]]
     * @param array<int, string>|CatalogSelectBuilder      $select
     * @param array<string, string>                    $order  Sort order, e.g. ['iblockId' => 'ASC']
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.catalog.list',
        'https://apidocs.bitrix24.com/api-reference/catalog/catalog/catalog-catalog-list-rest-v3.html',
        'Returns a list of trade catalogs',
        ApiVersion::v3
    )]
    public function list(
        array|FilterBuilderInterface $filter = [],
        array|CatalogSelectBuilder $select = [],
        array $order = [],
        ?CatalogListPagination $pagination = null,
    ): CatalogsResult {
        if ($filter instanceof FilterBuilderInterface) {
            $filter = $filter->toArray();
        }

        if ($select instanceof SelectBuilderInterface) {
            $select = $select->buildSelect();
        }

        return new CatalogsResult(
            $this->core->call(
                'catalog.catalog.list',
                $this->withoutEmpty([
                    'filter'     => $filter,
                    'select'     => $select,
                    'order'      => $order,
                    'pagination' => $pagination?->toArray(),
                ]),
                ApiVersion::v3
            )
        );
    }

    /**
     * Turns an existing information block into a trade catalog and returns the created record.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/catalog/catalog-catalog-add.html
     *
     * @param array<string, mixed>|CatalogItemBuilder $fields iblockId of an existing information block is required
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.catalog.add',
        'https://apidocs.bitrix24.com/api-reference/catalog/catalog/catalog-catalog-add.html',
        'Adds a trade catalog',
        ApiVersion::v3
    )]
    public function add(array|CatalogItemBuilder $fields): CatalogResult
    {
        if ($fields instanceof ItemBuilderInterface) {
            $fields = $fields->build();
        }

        return new CatalogResult($this->core->call('catalog.catalog.add', ['fields' => $fields], ApiVersion::v3));
    }

    /**
     * Updates a trade catalog and returns the updated record.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/catalog/catalog-catalog-update.html
     *
     * @param array<string, mixed>|CatalogItemBuilder $fields
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.catalog.update',
        'https://apidocs.bitrix24.com/api-reference/catalog/catalog/catalog-catalog-update.html',
        'Updates a trade catalog',
        ApiVersion::v3
    )]
    public function update(int $id, array|CatalogItemBuilder $fields): CatalogResult
    {
        if ($fields instanceof ItemBuilderInterface) {
            $fields = $fields->build();
        }

        return new CatalogResult(
            $this->core->call('catalog.catalog.update', ['id' => $id, 'fields' => $fields], ApiVersion::v3)
        );
    }

    /**
     * Removes the trade catalog flag; the underlying information block and its elements are kept.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/catalog/catalog-catalog-delete.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.catalog.delete',
        'https://apidocs.bitrix24.com/api-reference/catalog/catalog/catalog-catalog-delete.html',
        'Removes the trade catalog flag from an information block',
        ApiVersion::v3
    )]
    public function delete(int $id): DeletedCatalogResult
    {
        return new DeletedCatalogResult($this->core->call('catalog.catalog.delete', ['id' => $id], ApiVersion::v3));
    }

    /**
     * Checks whether a trade catalog stores product offers (SKU).
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/catalog/catalog-catalog-is-offers-rest-v3.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.catalog.isoffers',
        'https://apidocs.bitrix24.com/api-reference/catalog/catalog/catalog-catalog-is-offers-rest-v3.html',
        'Checks whether a trade catalog stores product offers',
        ApiVersion::v3
    )]
    public function isOffers(int $id): IsOffersCatalogResult
    {
        return new IsOffersCatalogResult($this->core->call('catalog.catalog.isoffers', ['id' => $id], ApiVersion::v3));
    }

    /**
     * @param array<string, mixed> $parameters
     * @return array<string, mixed>
     */
    private function withoutEmpty(array $parameters): array
    {
        return array_filter($parameters, static fn (mixed $value): bool => $value !== null && $value !== []);
    }
}
