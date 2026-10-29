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

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Result\AddedItemBatchResult;
use Bitrix24\SDK\Core\Result\UpdatedItemBatchResult;
use Bitrix24\SDK\Services\AbstractBatchService;
use Bitrix24\SDK\Services\CRM\CallList\Result\CallListItemResult;
use Generator;

#[ApiBatchServiceMetadata(new Scope(['crm']))]
class Batch extends AbstractBatchService
{
    /**
     * @param list<array{ENTITY_TYPE: 'CONTACT'|'COMPANY', ENTITIES: list<int>, WEBFORM_ID?: int}> $callLists
     * @return Generator<int, AddedItemBatchResult>
     */
    #[ApiBatchMethodMetadata('crm.calllist.add', 'https://apidocs.bitrix24.com/api-reference/crm/call-list/crm-calllist-add.html', 'Create call lists in batch mode')]
    public function add(array $callLists): Generator
    {
        foreach ($this->batch->addEntityItems('crm.calllist.add', $callLists) as $key => $item) {
            yield $key => new AddedItemBatchResult($item);
        }
    }

    /**
     * Omitting WEBFORM_ID removes the existing CRM form association.
     *
     * @param array<int, array{ENTITY_TYPE: 'CONTACT'|'COMPANY', ENTITIES: list<int>, WEBFORM_ID?: int}> $callLists Keyed by call list ID.
     * @return Generator<int, UpdatedItemBatchResult>
     */
    #[ApiBatchMethodMetadata('crm.calllist.update', 'https://apidocs.bitrix24.com/api-reference/crm/call-list/crm-calllist-update.html', 'Update call lists in batch mode')]
    public function update(array $callLists): Generator
    {
        foreach ($this->batch->updateEntityItems('crm.calllist.update', $callLists) as $key => $item) {
            yield $key => new UpdatedItemBatchResult($item);
        }
    }

    /**
     * @param array<string, string> $order
     * @param array<string, mixed> $filter
     * @param list<string> $select
     * @return Generator<int, CallListItemResult>
     */
    #[ApiBatchMethodMetadata('crm.calllist.list', 'https://apidocs.bitrix24.com/api-reference/crm/call-list/crm-calllist-list.html', 'Iterate call lists in batch mode')]
    public function list(array $order = [], array $filter = [], array $select = [], ?int $limit = null): Generator
    {
        foreach ($this->batch->getTraversableList('crm.calllist.list', $order, $filter, $select, $limit) as $key => $item) {
            yield $key => new CallListItemResult($item);
        }
    }
}
