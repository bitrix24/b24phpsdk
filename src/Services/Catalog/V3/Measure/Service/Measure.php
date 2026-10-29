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

namespace Bitrix24\SDK\Services\Catalog\V3\Measure\Service;

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
use Bitrix24\SDK\Services\Catalog\V3\Measure\Result\DeletedMeasureResult;
use Bitrix24\SDK\Services\Catalog\V3\Measure\Result\MeasureResult;
use Bitrix24\SDK\Services\Catalog\V3\Measure\Result\MeasuresResult;

#[ApiServiceMetadata(new Scope(['catalog']))]
class Measure extends AbstractService
{
    /**
     * Returns a unit of measure by ID.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/measure/catalog-measure-get-rest-v3.html
     *
     * @param array<int, string>|MeasureSelectBuilder $select
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.measure.get',
        'https://apidocs.bitrix24.com/api-reference/catalog/measure/catalog-measure-get-rest-v3.html',
        'Returns a unit of measure by ID',
        ApiVersion::v3
    )]
    public function get(int $id, array|MeasureSelectBuilder $select = []): MeasureResult
    {
        if ($select instanceof SelectBuilderInterface) {
            $select = $select->buildSelect();
        }

        return new MeasureResult(
            $this->core->call('catalog.measure.get', $this->withoutEmpty(['id' => $id, 'select' => $select]), ApiVersion::v3)
        );
    }

    /**
     * Returns a list of units of measure.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/measure/catalog-measure-list-rest-v3.html
     *
     * @param array<int, mixed>|FilterBuilderInterface $filter Conditions such as [['code', '=', 796]]
     * @param array<int, string>|MeasureSelectBuilder      $select
     * @param array<string, string>                    $order  Sort order, e.g. ['code' => 'ASC']
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.measure.list',
        'https://apidocs.bitrix24.com/api-reference/catalog/measure/catalog-measure-list-rest-v3.html',
        'Returns a list of units of measure',
        ApiVersion::v3
    )]
    public function list(
        array|FilterBuilderInterface $filter = [],
        array|MeasureSelectBuilder $select = [],
        array $order = [],
        ?MeasureListPagination $pagination = null,
    ): MeasuresResult {
        if ($filter instanceof FilterBuilderInterface) {
            $filter = $filter->toArray();
        }

        if ($select instanceof SelectBuilderInterface) {
            $select = $select->buildSelect();
        }

        return new MeasuresResult(
            $this->core->call(
                'catalog.measure.list',
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
     * Adds a unit of measure and returns the created record.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/measure/catalog-measure-add-rest-v3.html
     *
     * @param array<string, mixed>|MeasureItemBuilder $fields code and measureTitle are required
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.measure.add',
        'https://apidocs.bitrix24.com/api-reference/catalog/measure/catalog-measure-add-rest-v3.html',
        'Adds a unit of measure',
        ApiVersion::v3
    )]
    public function add(array|MeasureItemBuilder $fields): MeasureResult
    {
        if ($fields instanceof ItemBuilderInterface) {
            $fields = $fields->build();
        }

        return new MeasureResult($this->core->call('catalog.measure.add', ['fields' => $fields], ApiVersion::v3));
    }

    /**
     * Updates a unit of measure and returns the updated record.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/measure/catalog-measure-update-rest-v3.html
     *
     * @param array<string, mixed>|MeasureItemBuilder $fields
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.measure.update',
        'https://apidocs.bitrix24.com/api-reference/catalog/measure/catalog-measure-update-rest-v3.html',
        'Updates a unit of measure',
        ApiVersion::v3
    )]
    public function update(int $id, array|MeasureItemBuilder $fields): MeasureResult
    {
        if ($fields instanceof ItemBuilderInterface) {
            $fields = $fields->build();
        }

        return new MeasureResult(
            $this->core->call('catalog.measure.update', ['id' => $id, 'fields' => $fields], ApiVersion::v3)
        );
    }

    /**
     * Deletes a unit of measure.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/measure/catalog-measure-delete-rest-v3.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.measure.delete',
        'https://apidocs.bitrix24.com/api-reference/catalog/measure/catalog-measure-delete-rest-v3.html',
        'Deletes a unit of measure',
        ApiVersion::v3
    )]
    public function delete(int $id): DeletedMeasureResult
    {
        return new DeletedMeasureResult($this->core->call('catalog.measure.delete', ['id' => $id], ApiVersion::v3));
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
