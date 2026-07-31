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

namespace Bitrix24\SDK\Services\Catalog\Measure\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\FieldsResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Catalog\Measure\Result\AddedMeasureResult;
use Bitrix24\SDK\Services\Catalog\Measure\Result\DeletedMeasureResult;
use Bitrix24\SDK\Services\Catalog\Measure\Result\MeasureResult;
use Bitrix24\SDK\Services\Catalog\Measure\Result\MeasuresResult;
use Bitrix24\SDK\Services\Catalog\Measure\Result\UpdatedMeasureResult;

#[ApiServiceMetadata(new Scope(['catalog']))]
class Measure extends AbstractService
{
    /**
     * Creates a new measurement unit in the catalog.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/measure/catalog-measure-add.html
     *
     * @param array{
     *     code: int,
     *     measureTitle: string,
     *     isDefault?: string,
     *     symbol?: string,
     *     symbolIntl?: string,
     *     symbolLetterIntl?: string
     * } $fields
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.measure.add',
        'https://apidocs.bitrix24.com/api-reference/catalog/measure/catalog-measure-add.html',
        'Creates a new measurement unit in the catalog.'
    )]
    public function add(array $fields): AddedMeasureResult
    {
        return new AddedMeasureResult($this->core->call('catalog.measure.add', ['fields' => $fields]));
    }

    /**
     * Updates a measurement unit in the catalog.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/measure/catalog-measure-update.html
     *
     * @param array{
     *     code?: int,
     *     measureTitle?: string,
     *     isDefault?: string,
     *     symbol?: string,
     *     symbolIntl?: string,
     *     symbolLetterIntl?: string
     * } $fields
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.measure.update',
        'https://apidocs.bitrix24.com/api-reference/catalog/measure/catalog-measure-update.html',
        'Updates a measurement unit in the catalog.'
    )]
    public function update(int $id, array $fields): UpdatedMeasureResult
    {
        $this->guardPositiveId($id);

        return new UpdatedMeasureResult($this->core->call('catalog.measure.update', [
            'id' => $id,
            'fields' => $fields,
        ]));
    }

    /**
     * Returns information about a measurement unit by its identifier.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/measure/catalog-measure-get.html
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.measure.get',
        'https://apidocs.bitrix24.com/api-reference/catalog/measure/catalog-measure-get.html',
        'Returns information about a measurement unit by its identifier.'
    )]
    public function get(int $id): MeasureResult
    {
        $this->guardPositiveId($id);

        return new MeasureResult($this->core->call('catalog.measure.get', ['id' => $id]));
    }

    /**
     * Returns a list of measurement units from the catalog.
     *
     * Use MeasuresResult::getMeasures() for items and MeasuresResult::getTotal() for the total count.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/measure/catalog-measure-list.html
     *
     * @param string[]             $select
     * @param array<string, mixed> $filter
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.measure.list',
        'https://apidocs.bitrix24.com/api-reference/catalog/measure/catalog-measure-list.html',
        'Returns a list of measurement units from the catalog.'
    )]
    public function list(array $select = [], array $filter = []): MeasuresResult
    {
        return new MeasuresResult($this->core->call('catalog.measure.list', [
            'select' => $select,
            'filter' => $filter,
        ]));
    }

    /**
     * Deletes a measurement unit from the catalog.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/measure/catalog-measure-delete.html
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.measure.delete',
        'https://apidocs.bitrix24.com/api-reference/catalog/measure/catalog-measure-delete.html',
        'Deletes a measurement unit from the catalog.'
    )]
    public function delete(int $id): DeletedMeasureResult
    {
        $this->guardPositiveId($id);

        return new DeletedMeasureResult($this->core->call('catalog.measure.delete', ['id' => $id]));
    }

    /**
     * Returns the available measurement unit fields in the catalog.
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/measure/catalog-measure-get-fields.html
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.measure.getFields',
        'https://apidocs.bitrix24.com/api-reference/catalog/measure/catalog-measure-get-fields.html',
        'Returns the available measurement unit fields in the catalog.'
    )]
    public function fields(): FieldsResult
    {
        return new FieldsResult($this->core->call('catalog.measure.getFields'));
    }
}
