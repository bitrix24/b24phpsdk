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

namespace Bitrix24\SDK\Services\CRM\CallList\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\AddedItemResult;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\CRM\CallList\Result\CallListResult;
use Bitrix24\SDK\Services\CRM\CallList\Result\CallListsResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['crm']))]
class CallList extends AbstractService
{
    public Batch $batch;

    /**
     * CallList constructor.
     *
     * @param Batch           $batch
     * @param CoreInterface   $core
     * @param LoggerInterface $log
     */
    public function __construct(Batch $batch, CoreInterface $core, LoggerInterface $log)
    {
        parent::__construct($core, $log);
        $this->batch = $batch;
    }

    /**
     * Add new calllist
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/call-list/crm-call-list-add.html
     *
     * @return AddedItemResult
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.calllist.add',
        'https://apidocs.bitrix24.com/api-reference/crm/call-list/crm-call-list-add.html',
        'Add new calllist'
    )]
    public function add(string $entityType, array $entities, int $webformId = 0): AddedItemResult
    {
        $params = [
            'ENTITY_TYPE' => $entityType,
            'ENTITIES' => $entities,
        ];
        if ($webformId !== 0) {
            $params['WEBFORM_ID'] = $webformId;
        }
        return new AddedItemResult(
            $this->core->call(
                'crm.calllist.add',
                $params
            )
        );
    }

    /**
     * Returns a calllist by the id.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/call-list/crm-call-list-get.html
     *
     * @param int $id
     *
     * @return CallListResult
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.calllist.get',
        'https://apidocs.bitrix24.com/api-reference/crm/call-list/crm-call-list-get.html',
        'Returns a calllist by the id.'
    )]
    public function get(int $id): CallListResult
    {
        return new CallListResult($this->core->call('crm.calllist.get', ['ID' => $id]));
    }

    /**
     * Get list of calllist items.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/call-list/crm-call-list-list.html
     *
     * @param array $order     - order of calllist items
     * @param array $filter    = ['ID','ENTITY_TYPE_ID','WEBFORM_ID','CREATED_BY_ID']
     * @param array $select    = ['ID','ENTITY_TYPE_ID','WEBFORM_ID','DATE_CREATE','CREATED_BY_ID']
     * @param int   $startItem - entity number to start from (usually returned in 'next' field of previous 'crm.calllist.list' API call)
     *
     * @return CallListsResult
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.calllist.list',
        'https://apidocs.bitrix24.com/api-reference/crm/call-list/crm-call-list-list.html',
        'Get list of calllist items.'
    )]
    public function list(array $order = [], array $filter = [], array $select = [], int $startItem = 0): CallListsResult
    {
        if ($select === []) {
            $select = ['ID','ENTITY_TYPE_ID','WEBFORM_ID','DATE_CREATE','CREATED_BY_ID'];
        }
        return new CallListsResult(
            $this->core->call(
                'crm.calllist.list',
                [
                    'ORDER'  => $order,
                    'FILTER' => $filter,
                    'SELECT' => $select,
                    'start'  => $startItem,
                ]
            )
        );
    }

    /**
     * Updates the specified (existing) calllist.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/call-list/crm-call-list-update.html
     *
     * @return UpdatedItemResult
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.calllist.update',
        'https://apidocs.bitrix24.com/api-reference/crm/call-list/crm-call-list-update.html',
        'Updates the specified (existing) calllist.'
    )]
    public function update(int $listId, string $entityType, array $entities, int $webformId = 0): UpdatedItemResult
    {
        $params = [
            'LIST_ID' => $listId,
            'ENTITY_TYPE' => $entityType,
            'ENTITIES' => $entities,
        ];
        if ($webformId !== 0) {
            $params['WEBFORM_ID'] = $webformId;
        }
        return new UpdatedItemResult(
            $this->core->call(
                'crm.calllist.update',
                $params
            )
        );
    }
    
    /**
     * Get list of calllist statuses.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/call-list/crm-call-list-statuslist.html
     *
     * @return CallListStatusesResult
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.calllist.statuslist',
        'https://apidocs.bitrix24.com/api-reference/crm/call-list/crm-call-list-statuslist.html',
        'Get list of calllist statuses.'
    )]
    public function statusList(): CallListStatusesResult
    {
        return new CallListStatusesResult(
            $this->core->call(
                'crm.calllist.statuslist', []
            )
        );
    }
    
    /**
     * Get list of calllist items.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/call-list/crm-call-list-items-get.html
     *
     * @return CallListItemsResult
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.calllist.items.get',
        'https://apidocs.bitrix24.com/api-reference/crm/call-list/crm-call-list-items-get.html',
        'Get list of calllist items.'
    )]
    public function getItems(int $listId, array $filter = []): CallListItemsResult
    {
        return new CallListItemsResult(
            $this->core->call(
                'crm.calllist.items.get',
                [
                    'LIST_ID' => $listId,
                    'FILTER' => $filter
                ]
            )
        );
    }
}
