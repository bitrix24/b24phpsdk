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

namespace Bitrix24\SDK\Services\Biconnector\Connector\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\FieldsResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Biconnector\Connector\Result\AddedConnectorResult;
use Bitrix24\SDK\Services\Biconnector\Connector\Result\ConnectorResult;
use Bitrix24\SDK\Services\Biconnector\Connector\Result\ConnectorsResult;
use Bitrix24\SDK\Services\Biconnector\Connector\Result\DeletedConnectorResult;
use Bitrix24\SDK\Services\Biconnector\Connector\Result\UpdatedConnectorResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['biconnector']))]
class Connector extends AbstractService
{
    /**
     * Connector constructor
     */
    public function __construct(public Batch $batch, CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Add a new connector
     *
     * @link https://apidocs.bitrix24.com/api-reference/biconnector/connector/biconnector-connector-add.html
     *
     * @param array{
     *   title: string,
     *   logo: string,
     *   urlCheck: string,
     *   urlData: string,
     *   urlTableList: string,
     *   urlTableDescription: string,
     *   settings: array,
     *   description?: string,
     *   sort?: int,
     * } $fields
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'biconnector.connector.add',
        'https://apidocs.bitrix24.com/api-reference/biconnector/connector/biconnector-connector-add.html',
        'Add a new connector'
    )]
    public function add(array $fields): AddedConnectorResult
    {
        return new AddedConnectorResult(
            $this->core->call(
                'biconnector.connector.add',
                [
                    'fields' => $fields,
                ]
            )
        );
    }

    /**
     * Update an existing connector
     *
     * @link https://apidocs.bitrix24.com/api-reference/biconnector/connector/biconnector-connector-update.html
     *
     * @param array{
     *   title?: string,
     *   logo?: string,
     *   description?: string,
     *   sort?: int,
     *   urlCheck?: string,
     *   urlData?: string,
     *   urlTableList?: string,
     *   urlTableDescription?: string,
     *   settings?: array,
     *   supportMapping?: bool,
     * } $fields
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'biconnector.connector.update',
        'https://apidocs.bitrix24.com/api-reference/biconnector/connector/biconnector-connector-update.html',
        'Update an existing connector'
    )]
    public function update(int $id, array $fields): UpdatedConnectorResult
    {
        return new UpdatedConnectorResult(
            $this->core->call(
                'biconnector.connector.update',
                [
                    'id'     => $id,
                    'fields' => $fields,
                ]
            )
        );
    }

    /**
     * Get a connector by its ID
     *
     * @link https://apidocs.bitrix24.com/api-reference/biconnector/connector/biconnector-connector-get.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'biconnector.connector.get',
        'https://apidocs.bitrix24.com/api-reference/biconnector/connector/biconnector-connector-get.html',
        'Get a connector by its ID'
    )]
    public function get(int $id): ConnectorResult
    {
        return new ConnectorResult(
            $this->core->call(
                'biconnector.connector.get',
                [
                    'id' => $id,
                ]
            )
        );
    }

    /**
     * Get a list of connectors
     *
     * @link https://apidocs.bitrix24.com/api-reference/biconnector/connector/biconnector-connector-list.html
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
        'biconnector.connector.list',
        'https://apidocs.bitrix24.com/api-reference/biconnector/connector/biconnector-connector-list.html',
        'Get a list of connectors'
    )]
    public function list(array $order = [], array $filter = [], array $select = [], int $page = 1): ConnectorsResult
    {
        return new ConnectorsResult(
            $this->core->call(
                'biconnector.connector.list',
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
     * Delete a connector by its ID
     *
     * @link https://apidocs.bitrix24.com/api-reference/biconnector/connector/biconnector-connector-delete.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'biconnector.connector.delete',
        'https://apidocs.bitrix24.com/api-reference/biconnector/connector/biconnector-connector-delete.html',
        'Delete a connector by its ID'
    )]
    public function delete(int $id): DeletedConnectorResult
    {
        return new DeletedConnectorResult(
            $this->core->call(
                'biconnector.connector.delete',
                [
                    'id' => $id,
                ]
            )
        );
    }

    /**
     * Get the fields description for connectors
     *
     * @link https://apidocs.bitrix24.com/api-reference/biconnector/connector/biconnector-connector-fields.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'biconnector.connector.fields',
        'https://apidocs.bitrix24.com/api-reference/biconnector/connector/biconnector-connector-fields.html',
        'Get the fields description for connectors'
    )]
    public function fields(): FieldsResult
    {
        return new FieldsResult($this->core->call('biconnector.connector.fields'));
    }

    /**
     * Count connectors
     *
     * Note: biconnector.connector.list does not return a total count in pagination,
     * so we iterate all available items via batch to count them.
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
