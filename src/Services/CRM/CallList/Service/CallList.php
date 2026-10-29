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

namespace Bitrix24\SDK\Services\CRM\CallList\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Result\AddedItemResult;
use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\CRM\CallList\Result\CallListItemsResult;
use Bitrix24\SDK\Services\CRM\CallList\Result\CallListResult;
use Bitrix24\SDK\Services\CRM\CallList\Result\CallListsResult;
use Bitrix24\SDK\Services\CRM\CallList\Result\CallListStatusesResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['crm']))]
class CallList extends AbstractService
{
    public function __construct(public Batch $batch, CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Use null to omit WEBFORM_ID. Bitrix24 rejects an explicit zero as an unknown form.
     *
     * @param 'CONTACT'|'COMPANY' $entityType
     * @param list<int> $entities
     */
    #[ApiEndpointMetadata('crm.calllist.add', 'https://apidocs.bitrix24.com/api-reference/crm/call-list/crm-calllist-add.html', 'Create a call list')]
    public function add(string $entityType, array $entities, ?int $webformId = null): AddedItemResult
    {
        $parameters = ['ENTITY_TYPE' => $entityType, 'ENTITIES' => $entities];
        if ($webformId !== null) {
            $parameters['WEBFORM_ID'] = $webformId;
        }

        return new AddedItemResult($this->core->call('crm.calllist.add', $parameters));
    }

    #[ApiEndpointMetadata('crm.calllist.get', 'https://apidocs.bitrix24.com/api-reference/crm/call-list/crm-calllist-get.html', 'Get a call list')]
    public function get(int $id): CallListResult
    {
        return new CallListResult($this->core->call('crm.calllist.get', ['ID' => $id]));
    }

    /**
     * @param array<string, string> $order
     * @param array<string, mixed> $filter
     * @param list<string> $select
     */
    #[ApiEndpointMetadata('crm.calllist.list', 'https://apidocs.bitrix24.com/api-reference/crm/call-list/crm-calllist-list.html', 'Get call lists')]
    public function list(array $order = [], array $filter = [], array $select = []): CallListsResult
    {
        return new CallListsResult($this->core->call('crm.calllist.list', ['ORDER' => $order, 'FILTER' => $filter, 'SELECT' => $select]));
    }

    /**
     * Passing null omits WEBFORM_ID; Bitrix24 then removes the existing CRM form association.
     * An explicit zero is forwarded unchanged and rejected by Bitrix24 as an unknown form.
     *
     * @param 'CONTACT'|'COMPANY' $entityType
     * @param list<int> $entities
     */
    #[ApiEndpointMetadata('crm.calllist.update', 'https://apidocs.bitrix24.com/api-reference/crm/call-list/crm-calllist-update.html', 'Update a call list')]
    public function update(int $listId, string $entityType, array $entities, ?int $webformId = null): UpdatedItemResult
    {
        $parameters = ['LIST_ID' => $listId, 'ENTITY_TYPE' => $entityType, 'ENTITIES' => $entities];
        if ($webformId !== null) {
            $parameters['WEBFORM_ID'] = $webformId;
        }

        return new UpdatedItemResult($this->core->call('crm.calllist.update', $parameters));
    }

    #[ApiEndpointMetadata('crm.calllist.statuslist', 'https://apidocs.bitrix24.com/api-reference/crm/call-list/crm-calllist-statuslist.html', 'Get call list participant statuses')]
    public function statusList(): CallListStatusesResult
    {
        return new CallListStatusesResult($this->core->call('crm.calllist.statuslist', []));
    }

    /**
     * @param array<string, mixed> $filter
     */
    #[ApiEndpointMetadata('crm.calllist.items.get', 'https://apidocs.bitrix24.com/api-reference/crm/call-list/crm-calllist-items-get.html', 'Get call list participants')]
    public function getItems(int $listId, array $filter = []): CallListItemsResult
    {
        return new CallListItemsResult($this->core->call('crm.calllist.items.get', ['LIST_ID' => $listId, 'FILTER' => $filter]));
    }
}
