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

namespace Bitrix24\SDK\Services\Biconnector\Source\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\FieldsResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Biconnector\Source\Result\AddedSourceResult;
use Bitrix24\SDK\Services\Biconnector\Source\Result\DeletedSourceResult;
use Bitrix24\SDK\Services\Biconnector\Source\Result\SourceResult;
use Bitrix24\SDK\Services\Biconnector\Source\Result\SourcesResult;
use Bitrix24\SDK\Services\Biconnector\Source\Result\UpdatedSourceResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['biconnector']))]
class Source extends AbstractService
{
    /**
     * Source constructor
     */
    public function __construct(public Batch $batch, CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Add a new data source
     *
     * @link https://apidocs.bitrix24.com/api-reference/biconnector/source/biconnector-source-add.html
     *
     * @param array{
     *   title: string,
     *   connectorId: int,
     *   description?: string,
     *   active?: bool,
     *   settings?: array,
     * } $fields
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'biconnector.source.add',
        'https://apidocs.bitrix24.com/api-reference/biconnector/source/biconnector-source-add.html',
        'Add a new data source'
    )]
    public function add(array $fields): AddedSourceResult
    {
        return new AddedSourceResult(
            $this->core->call(
                'biconnector.source.add',
                [
                    'fields' => $fields,
                ]
            )
        );
    }

    /**
     * Update an existing data source
     *
     * @link https://apidocs.bitrix24.com/api-reference/biconnector/source/biconnector-source-update.html
     *
     * @param array{
     *   title?: string,
     *   description?: string,
     *   active?: bool,
     *   settings?: array,
     * } $fields
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'biconnector.source.update',
        'https://apidocs.bitrix24.com/api-reference/biconnector/source/biconnector-source-update.html',
        'Update an existing data source'
    )]
    public function update(int $id, array $fields): UpdatedSourceResult
    {
        return new UpdatedSourceResult(
            $this->core->call(
                'biconnector.source.update',
                [
                    'id'     => $id,
                    'fields' => $fields,
                ]
            )
        );
    }

    /**
     * Get a data source by its ID
     *
     * @link https://apidocs.bitrix24.com/api-reference/biconnector/source/biconnector-source-get.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'biconnector.source.get',
        'https://apidocs.bitrix24.com/api-reference/biconnector/source/biconnector-source-get.html',
        'Get a data source by its ID'
    )]
    public function get(int $id): SourceResult
    {
        return new SourceResult(
            $this->core->call(
                'biconnector.source.get',
                [
                    'id' => $id,
                ]
            )
        );
    }

    /**
     * Get a list of data sources
     *
     * @link https://apidocs.bitrix24.com/api-reference/biconnector/source/biconnector-source-list.html
     *
     * @param array $order  - sort fields, e.g. ['id' => 'ASC']
     * @param array $filter - filter fields
     * @param array $select - fields to include in the result
     * @param int   $page   - page number for pagination (page size is 50 records per page)
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'biconnector.source.list',
        'https://apidocs.bitrix24.com/api-reference/biconnector/source/biconnector-source-list.html',
        'Get a list of data sources'
    )]
    public function list(array $order = [], array $filter = [], array $select = [], int $page = 1): SourcesResult
    {
        return new SourcesResult(
            $this->core->call(
                'biconnector.source.list',
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
     * Delete a data source by its ID
     *
     * @link https://apidocs.bitrix24.com/api-reference/biconnector/source/biconnector-source-delete.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'biconnector.source.delete',
        'https://apidocs.bitrix24.com/api-reference/biconnector/source/biconnector-source-delete.html',
        'Delete a data source by its ID'
    )]
    public function delete(int $id): DeletedSourceResult
    {
        return new DeletedSourceResult(
            $this->core->call(
                'biconnector.source.delete',
                [
                    'id' => $id,
                ]
            )
        );
    }

    /**
     * Get the fields description for data sources
     *
     * @link https://apidocs.bitrix24.com/api-reference/biconnector/source/biconnector-source-fields.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'biconnector.source.fields',
        'https://apidocs.bitrix24.com/api-reference/biconnector/source/biconnector-source-fields.html',
        'Get the fields description for data sources'
    )]
    public function fields(): FieldsResult
    {
        return new FieldsResult($this->core->call('biconnector.source.fields'));
    }

    /**
     * Count data sources
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
