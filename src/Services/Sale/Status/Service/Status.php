<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Sally Fancen <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Sale\Status\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Sale\Status\Result\StatusAddResult;
use Bitrix24\SDK\Services\Sale\Status\Result\StatusFieldsResult;
use Bitrix24\SDK\Services\Sale\Status\Result\StatusesResult;
use Bitrix24\SDK\Services\Sale\Status\Result\StatusResult;
use Bitrix24\SDK\Services\Sale\Status\Result\StatusUpdateResult;
use Psr\Log\LoggerInterface;

/**
 * Class Status - service for working with sale.status.* methods
 *
 * @package Bitrix24\SDK\Services\Sale\Status\Service
 */
#[ApiServiceMetadata(new Scope(['sale']))]
class Status extends AbstractService
{
    /**
     * Status constructor
     */
    public function __construct(CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Add a new status
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/status/sale-status-add.html
     *
     * @param array $fields Fields for the new status
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.status.add',
        'https://apidocs.bitrix24.com/api-reference/sale/status/sale-status-add.html',
        'Adds a new status'
    )]
    public function add(array $fields): StatusAddResult
    {
        return new StatusAddResult(
            $this->core->call('sale.status.add', [
                'fields' => $fields
            ])
        );
    }

    /**
     * Update an existing status
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/status/sale-status-update.html
     *
     * @param string $id Status ID
     * @param array $fields Fields to update
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.status.update',
        'https://apidocs.bitrix24.com/api-reference/sale/status/sale-status-update.html',
        'Updates an existing status'
    )]
    public function update(string $id, array $fields): StatusUpdateResult
    {
        return new StatusUpdateResult(
            $this->core->call('sale.status.update', [
                'id' => $id,
                'fields' => $fields
            ])
        );
    }

    /**
     * Get status by ID
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/status/sale-status-get.html
     *
     * @param string $id Status ID
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.status.get',
        'https://apidocs.bitrix24.com/api-reference/sale/status/sale-status-get.html',
        'Returns status details by ID'
    )]
    public function get(string $id): StatusResult
    {
        return new StatusResult(
            $this->core->call('sale.status.get', [
                'id' => $id
            ])
        );
    }

    /**
     * Get list of statuses
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/status/sale-status-list.html
     *
     * @param array $select Fields to select
     * @param array $filter Filter parameters
     * @param array $order Order parameters
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.status.list',
        'https://apidocs.bitrix24.com/api-reference/sale/status/sale-status-list.html',
        'Returns a list of statuses'
    )]
    public function list(array $select = [], array $filter = [], array $order = []): StatusesResult
    {
        return new StatusesResult(
            $this->core->call('sale.status.list', [
                'select' => $select,
                'filter' => $filter,
                'order' => $order
            ])
        );
    }

    /**
     * Delete a status
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/status/sale-status-delete.html
     *
     * @param string $id Status ID
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.status.delete',
        'https://apidocs.bitrix24.com/api-reference/sale/status/sale-status-delete.html',
        'Deletes a status'
    )]
    public function delete(string $id): DeletedItemResult
    {
        return new DeletedItemResult(
            $this->core->call('sale.status.delete', [
                'id' => $id
            ])
        );
    }

    /**
     * Get available status fields
     *
     * @link https://apidocs.bitrix24.com/api-reference/sale/status/sale-status-getfields.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.status.getFields',
        'https://apidocs.bitrix24.com/api-reference/sale/status/sale-status-getfields.html',
        'Returns available fields and their settings'
    )]
    public function getFields(): StatusFieldsResult
    {
        return new StatusFieldsResult(
            $this->core->call('sale.status.getFields', [])
        );
    }
}
