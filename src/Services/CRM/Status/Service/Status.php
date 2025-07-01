<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Vadim Soluyanov <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\CRM\Status\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\AddedItemResult;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Core\Result\FieldsResult;
use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\CRM\Status\Result\StatusResult;
use Bitrix24\SDK\Services\CRM\Status\Result\StatusesResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['crm']))]
class Status extends AbstractService
{
    /**
     * Status constructor.
     */
    public function __construct(public Batch $batch, CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Creates a new element in the specified reference book
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/status/crm-status-add.html
     *
     * @param array{
     *   ID?: int,
     *   ENTITY_ID?: string,
     *   STATUS_ID?: string,
     *   SORT?: int,
     *   NAME?: string,
     *   SYSTEM?: bool,
     *   CATEGORY_ID?: int,
     *   COLOR?: string,
     *   SEMANTICS?: string,
     *   EXTRA?: array,
     * } $fields
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.status.add',
        'https://apidocs.bitrix24.com/api-reference/crm/status/crm-status-add.html',
        'Creates a new element in the specified reference book'
    )]
    public function add(array $fields): AddedItemResult
    {
        return new AddedItemResult(
            $this->core->call(
                'crm.status.add',
                [
                    'fields' => $fields
                ]
            )
        );
    }

    /**
     * Deletes an element from the reference book.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/status/crm-status-delete.html
     *
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.status.delete',
        'https://apidocs.bitrix24.com/api-reference/crm/status/crm-status-delete.html',
        'Deletes an element from the reference book'
    )]
    public function delete(int $id, bool $forced = false): DeletedItemResult
    {
        $param = $forced ? 'Y' : 'N';
        return new DeletedItemResult(
            $this->core->call(
                'crm.status.delete',
                [
                    'id' => $id,
                    'params' => ['FORCED' => $param]
                ]
            )
        );
    }

    /**
     * Returns descriptions of reference book fields.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/status/crm-status-fields.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.status.fields',
        'https://apidocs.bitrix24.com/api-reference/crm/status/crm-status-fields.html',
        'Returns descriptions of reference book fields'
    )]
    public function fields(): FieldsResult
    {
        return new FieldsResult($this->core->call('crm.status.fields'));
    }

    /**
     * Returns an element of the reference book by its identifier.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/status/crm-status-get.html
     *
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.status.get',
        'https://apidocs.bitrix24.com/api-reference/crm/status/crm-status-get.html',
        'Returns an element of the reference book by its identifier'
    )]
    public function get(int $id): StatusResult
    {
        return new StatusResult($this->core->call('crm.status.get', ['id' => $id]));
    }

    /**
     * Returns a list of elements of the reference book by filter.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/status/crm-status-list.html
     *
     * @param array   $order     - order of status items
     * @param array   $filter    - filter array
     * @param array   $select    = ['ID','ENTITY_ID','STATUS_ID','SORT','NAME','NAME_INIT','SYSTEM','CATEGORY_ID','COLOR','SEMANTICS','EXTRA']
     * @param integer $startItem - entity number to start from (usually returned in 'next' field of previous 'crm.status.list' API call)
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.status.list',
        'https://apidocs.bitrix24.com/api-reference/crm/status/crm-status-list.html',
        'Returns a list of elements of the reference book by filter'
    )]
    public function list(array $order = [], array $filter = [], array $select = [], int $startItem = 0): StatusesResult
    {
        return new StatusesResult(
            $this->core->call(
                'crm.status.list',
                [
                    'order'  => $order,
                    'filter' => $filter,
                    'select' => $select,
                    'start'  => $startItem,
                ]
            )
        );
    }

    /**
     * Updates an existing element of the reference book.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/status/crm-status-update.html
     *
     * @param array{
     *   ID?: int,
     *   ENTITY_ID?: string,
     *   STATUS_ID?: string,
     *   SORT?: int,
     *   NAME?: string,
     *   NAME_INIT?: string,
     *   SYSTEM?: bool,
     *   CATEGORY_ID?: int,
     *   COLOR?: string,
     *   SEMANTICS?: string,
     *   EXTRA?: array,
     *   }        $fields
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.status.update',
        'https://apidocs.bitrix24.com/api-reference/crm/status/crm-status-update.html',
        'Updates an existing element of the reference book'
    )]
    public function update(int $id, array $fields): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call(
                'crm.status.update',
                [
                    'id'     => $id,
                    'fields' => $fields
                ]
            )
        );
    }

    /**
     * Count statuses by filter
     *
     * @param array{
     *   ID?: int,
     *   ENTITY_ID?: string,
     *   STATUS_ID?: string,
     *   SORT?: int,
     *   NAME?: string,
     *   NAME_INIT?: string,
     *   SYSTEM?: bool,
     *   CATEGORY_ID?: int,
     *   COLOR?: string,
     *   SEMANTICS?: string,
     *   EXTRA?: array,
     *   } $filter
     *
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     * @throws \Bitrix24\SDK\Core\Exceptions\TransportException
     */
    public function countByFilter(array $filter = []): int
    {
        return $this->list([], $filter, ['ID'], 1)->getCoreResponse()->getResponseData()->getPagination()->getTotal();
    }
}
