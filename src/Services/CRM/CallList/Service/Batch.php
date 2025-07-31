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

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AddedItemBatchResult;
use Bitrix24\SDK\Core\Result\UpdatedItemBatchResult;
use Bitrix24\SDK\Services\AbstractBatchService;
use Bitrix24\SDK\Services\CRM\CallList\Result\CallListItemResult;
use Generator;

#[ApiBatchServiceMetadata(new Scope(['crm']))]
class Batch extends AbstractBatchService
{
    /**
     * batch calllist list method
     *
     * @param array    $order     - order of calllist items
     * @param array    $filter    = ['ID','ENTITY_TYPE_ID','WEBFORM_ID','CREATED_BY_ID']
     * @param array    $select = ['ID','ENTITY_TYPE_ID','WEBFORM_ID','DATE_CREATE','CREATED_BY_ID']
     *
     * @return Generator<int, CallListItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'crm.calllist.list',
        'https://apidocs.bitrix24.com/api-reference/crm/call-list/crm-call-list-list.html',
        'batch calllist list method'
    )]
    public function list(array $order = [], array $filter = [], array $select = [], ?int $limit = null): Generator
    {
        if ($select === []) {
            $select = ['ID','ENTITY_TYPE_ID','WEBFORM_ID','DATE_CREATE','CREATED_BY_ID'];
        }

        $this->log->debug(
            'list',
            [
                'order'  => $order,
                'filter' => $filter,
                'select' => $select,
                'limit'  => $limit,
            ]
        );
        foreach ($this->batch->getTraversableList('crm.calllist.list', $order, $filter, $select, $limit) as $key => $value) {
            yield $key => new CallListItemResult($value);
        }
    }

    /**
     * Batch adding calllist
     *
     * @param array <int, array{
     *                         ENTITY_TYPE?: string,
     *                         ENTITIES?: array,
     *                         WEBFORM_ID?: int,
     *                         }> $calllists
     *
     * @return Generator<int, AddedItemBatchResult>
     */
    #[ApiBatchMethodMetadata(
        'crm.calllist.add',
        'https://apidocs.bitrix24.com/api-reference/crm/call-list/crm-call-list-add.html',
        'Batch adding calllist'
    )]
    public function add(array $calllists): Generator
    {
        foreach ($this->batch->addEntityItems('crm.calllist.add', $calllists) as $key => $item) {
            yield $key => new AddedItemBatchResult($item);
        }
    }

    /**
     * Batch update calllists
     *
     * @param array<int, array> $calllistItems
     * @return Generator<int, UpdatedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'crm.calllist.update',
        'https://apidocs.bitrix24.com/api-reference/crm/call-list/crm-call-list-update.html',
        'Update in batch mode a list of crm.calllists'
    )]
    public function update(array $calllistItems): Generator
    {
        foreach ($this->batch->updateEntityItems('crm.calllist.update', $calllistItems) as $key => $item) {
            yield $key => new UpdatedItemBatchResult($item);
        }
    }
}
