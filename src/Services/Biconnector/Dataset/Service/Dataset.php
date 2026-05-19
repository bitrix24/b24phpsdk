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

namespace Bitrix24\SDK\Services\Biconnector\Dataset\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\FieldsResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Biconnector\Dataset\Result\AddedDatasetResult;
use Bitrix24\SDK\Services\Biconnector\Dataset\Result\DatasetResult;
use Bitrix24\SDK\Services\Biconnector\Dataset\Result\DatasetsResult;
use Bitrix24\SDK\Services\Biconnector\Dataset\Result\DeletedDatasetResult;
use Bitrix24\SDK\Services\Biconnector\Dataset\Result\UpdatedDatasetResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['biconnector']))]
class Dataset extends AbstractService
{
    /**
     * Dataset constructor
     */
    public function __construct(public Batch $batch, CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Add a new dataset
     *
     * @link https://apidocs.bitrix24.com/api-reference/biconnector/dataset/biconnector-dataset-add.html
     *
     * @param array{
     *   name: string,
     *   externalName: string,
     *   externalCode: string,
     *   sourceId: int,
     *   description?: string,
     *   fields?: array<int, array{name: string, externalCode: string, type: string}>,
     * } $fields
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'biconnector.dataset.add',
        'https://apidocs.bitrix24.com/api-reference/biconnector/dataset/biconnector-dataset-add.html',
        'Add a new dataset'
    )]
    public function add(array $fields): AddedDatasetResult
    {
        return new AddedDatasetResult(
            $this->core->call(
                'biconnector.dataset.add',
                [
                    'fields' => $fields,
                ]
            )
        );
    }

    /**
     * Update an existing dataset
     *
     * @link https://apidocs.bitrix24.com/api-reference/biconnector/dataset/biconnector-dataset-update.html
     *
     * @param array{
     *   description?: string,
     * } $fields
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'biconnector.dataset.update',
        'https://apidocs.bitrix24.com/api-reference/biconnector/dataset/biconnector-dataset-update.html',
        'Update an existing dataset'
    )]
    public function update(int $id, array $fields): UpdatedDatasetResult
    {
        return new UpdatedDatasetResult(
            $this->core->call(
                'biconnector.dataset.update',
                [
                    'id'     => $id,
                    'fields' => $fields,
                ]
            )
        );
    }

    /**
     * Get a dataset by its ID
     *
     * @link https://apidocs.bitrix24.com/api-reference/biconnector/dataset/biconnector-dataset-get.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'biconnector.dataset.get',
        'https://apidocs.bitrix24.com/api-reference/biconnector/dataset/biconnector-dataset-get.html',
        'Get a dataset by its ID'
    )]
    public function get(int $id): DatasetResult
    {
        return new DatasetResult(
            $this->core->call(
                'biconnector.dataset.get',
                [
                    'id' => $id,
                ]
            )
        );
    }

    /**
     * Get a list of datasets
     *
     * @link https://apidocs.bitrix24.com/api-reference/biconnector/dataset/biconnector-dataset-list.html
     *
     * @param array $order  - sort fields, e.g. ['dateCreate' => 'DESC']
     * @param array $filter - filter fields
     * @param array $select - fields to include in the result
     * @param int   $page   - page number for pagination (page size is 50 records per page)
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'biconnector.dataset.list',
        'https://apidocs.bitrix24.com/api-reference/biconnector/dataset/biconnector-dataset-list.html',
        'Get a list of datasets'
    )]
    public function list(array $order = [], array $filter = [], array $select = [], int $page = 1): DatasetsResult
    {
        return new DatasetsResult(
            $this->core->call(
                'biconnector.dataset.list',
                [
                    'order'  => $order,
                    'filter' => $filter,
                    'select' => $select,
                    'page'   => $page,
                ]
            )
        );
    }

    /**
     * Delete a dataset by its ID
     *
     * @link https://apidocs.bitrix24.com/api-reference/biconnector/dataset/biconnector-dataset-delete.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'biconnector.dataset.delete',
        'https://apidocs.bitrix24.com/api-reference/biconnector/dataset/biconnector-dataset-delete.html',
        'Delete a dataset by its ID'
    )]
    public function delete(int $id): DeletedDatasetResult
    {
        return new DeletedDatasetResult(
            $this->core->call(
                'biconnector.dataset.delete',
                [
                    'id' => $id,
                ]
            )
        );
    }

    /**
     * Get the fields description for datasets
     *
     * @link https://apidocs.bitrix24.com/api-reference/biconnector/dataset/biconnector-dataset-fields.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'biconnector.dataset.fields',
        'https://apidocs.bitrix24.com/api-reference/biconnector/dataset/biconnector-dataset-fields.html',
        'Get the fields description for datasets'
    )]
    public function fields(): FieldsResult
    {
        return new FieldsResult($this->core->call('biconnector.dataset.fields'));
    }

    /**
     * Update fields of an existing dataset (add, update visibility, or delete dataset columns)
     *
     * @link https://apidocs.bitrix24.com/api-reference/biconnector/dataset/biconnector-dataset-fields-update.html
     *
     * @param array<int, array{name: string, externalCode: string, type: string}> $add    - fields to add
     * @param array<int, array{id: int, visible: bool}>                           $update - fields to update (visibility)
     * @param int[]                                                                $delete - field IDs to delete
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'biconnector.dataset.fields.update',
        'https://apidocs.bitrix24.com/api-reference/biconnector/dataset/biconnector-dataset-fields-update.html',
        'Update fields of an existing dataset'
    )]
    public function updateFields(int $id, array $add = [], array $update = [], array $delete = []): UpdatedDatasetResult
    {
        $params = ['id' => $id];

        if ($add !== []) {
            $params['add'] = $add;
        }

        if ($update !== []) {
            $params['update'] = $update;
        }

        if ($delete !== []) {
            $params['delete'] = $delete;
        }

        return new UpdatedDatasetResult(
            $this->core->call('biconnector.dataset.fields.update', $params)
        );
    }

    /**
     * Count datasets
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function count(): int
    {
        $count = 0;
        foreach ($this->batch->list() as $item) {
            $count++;
        }

        return $count;
    }
}

