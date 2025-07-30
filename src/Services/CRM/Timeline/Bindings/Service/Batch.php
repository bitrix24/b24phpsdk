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

namespace Bitrix24\SDK\Services\CRM\Timeline\Bindings\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AddedItemBatchResult;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Core\Result\UpdatedItemBatchResult;
use Bitrix24\SDK\Services\CRM\Timeline\Bindings\Result\BindingItemResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['crm']))]
class Batch
{
    /**
     * Batch constructor.
     */
    public function __construct(protected BatchOperationsInterface $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch list method for bindings
     *
     * @param array{
     *   OWNER_ID?: int,
     *   ENTITY_ID?: int,
     *   ENTITY_TYPE?: string,
     *   } $filter
     *
     * @return Generator<int, BindingItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'crm.timeline.bindings.list',
        'https://apidocs.bitrix24.com/api-reference/crm/timeline/bindings/crm-timeline-bindings-list.html',
        'Batch list method for bindings'
    )]
    public function list(array $filter, ?int $limit = null): Generator
    {
        $this->log->debug(
            'batchList',
            [
                'limit'  => $limit,
            ]
        );
        foreach ($this->batch->getTraversableList('crm.timeline.bindings.list', [], $filter, [], $limit) as $key => $value) {
            yield $key => new BindingItemResult($value);
        }
    }

    /**
     * Batch adding bindings
     *
     * @param array <int, array{
     *   OWNER_ID?: int,
     *   ENTITY_ID?: int,
     *   ENTITY_TYPE?: string,
     *   }> $bindings
     *
     * @return Generator<int, UpdatedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'crm.timeline.bindings.bind',
        'https://apidocs.bitrix24.com/api-reference/crm/timeline/bindings/crm-timeline-bindings-bind.html',
        'Batch adding bindings'
    )]
    public function bind(array $bindings): Generator
    {
        $items = [];
        foreach ($bindings as $item) {
            $items[] = [
                'fields' => $item,
            ];
        }

        foreach ($this->batch->addEntityItems('crm.timeline.bindings.bind', $items) as $key => $item) {
            yield $key => new UpdatedItemBatchResult($item);
        }
    }

    /**
     * Batch delete bindings
     *
     * @param array <int, array{
     *   OWNER_ID?: int,
     *   ENTITY_ID?: int,
     *   ENTITY_TYPE?: string,
     *   }> $bindings
     *
     * @return Generator<int, DeletedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'crm.timeline.bindings.unbind',
        'https://apidocs.bitrix24.com/api-reference/crm/timeline/bindings/crm-timeline-bindings-unbind.html',
        'Batch delete bindings'
    )]
    public function unbind(array $bindings): Generator
    {
        foreach ($this->batch->deleteEntityItems('crm.timeline.bindings.unbind', $bindings) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }
}
