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

namespace Bitrix24\SDK\Services\Catalog\V3\Vat\Service;

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
use Bitrix24\SDK\Services\Catalog\V3\Vat\Result\DeletedVatResult;
use Bitrix24\SDK\Services\Catalog\V3\Vat\Result\VatResult;
use Bitrix24\SDK\Services\Catalog\V3\Vat\Result\VatsResult;

#[ApiServiceMetadata(new Scope(['catalog']))]
class Vat extends AbstractService
{
    /**
     * Returns a VAT rate by ID.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/vat/catalog-vat-get-rest-v3.html
     *
     * @param array<int, string>|VatSelectBuilder $select
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.vat.get',
        'https://apidocs.bitrix24.com/api-reference/catalog/vat/catalog-vat-get-rest-v3.html',
        'Returns a VAT rate by ID',
        ApiVersion::v3
    )]
    public function get(int $id, array|VatSelectBuilder $select = []): VatResult
    {
        if ($select instanceof SelectBuilderInterface) {
            $select = $select->buildSelect();
        }

        return new VatResult(
            $this->core->call('catalog.vat.get', $this->withoutEmpty(['id' => $id, 'select' => $select]), ApiVersion::v3)
        );
    }

    /**
     * Returns a list of VAT rates.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/vat/catalog-vat-list-rest-v3.html
     *
     * @param array<int, mixed>|FilterBuilderInterface $filter Conditions such as [['rate', '>=', 10]]
     * @param array<int, string>|VatSelectBuilder      $select
     * @param array<string, string>                    $order  Sort order, e.g. ['sort' => 'ASC']
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.vat.list',
        'https://apidocs.bitrix24.com/api-reference/catalog/vat/catalog-vat-list-rest-v3.html',
        'Returns a list of VAT rates',
        ApiVersion::v3
    )]
    public function list(
        array|FilterBuilderInterface $filter = [],
        array|VatSelectBuilder $select = [],
        array $order = [],
        ?VatListPagination $pagination = null,
    ): VatsResult {
        if ($filter instanceof FilterBuilderInterface) {
            $filter = $filter->toArray();
        }

        if ($select instanceof SelectBuilderInterface) {
            $select = $select->buildSelect();
        }

        return new VatsResult(
            $this->core->call(
                'catalog.vat.list',
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
     * Adds a VAT rate and returns the created record.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/vat/catalog-vat-add-rest-v3.html
     *
     * @param array<string, mixed>|VatItemBuilder $fields name and rate are required
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.vat.add',
        'https://apidocs.bitrix24.com/api-reference/catalog/vat/catalog-vat-add-rest-v3.html',
        'Adds a VAT rate',
        ApiVersion::v3
    )]
    public function add(array|VatItemBuilder $fields): VatResult
    {
        if ($fields instanceof ItemBuilderInterface) {
            $fields = $fields->build();
        }

        return new VatResult($this->core->call('catalog.vat.add', ['fields' => $fields], ApiVersion::v3));
    }

    /**
     * Updates a VAT rate and returns the updated record.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/vat/catalog-vat-update-rest-v3.html
     *
     * @param array<string, mixed>|VatItemBuilder $fields
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.vat.update',
        'https://apidocs.bitrix24.com/api-reference/catalog/vat/catalog-vat-update-rest-v3.html',
        'Updates a VAT rate',
        ApiVersion::v3
    )]
    public function update(int $id, array|VatItemBuilder $fields): VatResult
    {
        if ($fields instanceof ItemBuilderInterface) {
            $fields = $fields->build();
        }

        return new VatResult(
            $this->core->call('catalog.vat.update', ['id' => $id, 'fields' => $fields], ApiVersion::v3)
        );
    }

    /**
     * Deletes a VAT rate.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/vat/catalog-vat-delete-rest-v3.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.vat.delete',
        'https://apidocs.bitrix24.com/api-reference/catalog/vat/catalog-vat-delete-rest-v3.html',
        'Deletes a VAT rate',
        ApiVersion::v3
    )]
    public function delete(int $id): DeletedVatResult
    {
        return new DeletedVatResult($this->core->call('catalog.vat.delete', ['id' => $id], ApiVersion::v3));
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
