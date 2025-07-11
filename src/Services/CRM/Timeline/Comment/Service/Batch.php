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

namespace Bitrix24\SDK\Services\CRM\Timeline\Comment\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AddedItemBatchResult;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Core\Result\UpdatedItemBatchResult;
use Bitrix24\SDK\Services\CRM\Timeline\Comment\Result\CommentItemResult;
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
     * Batch list method for comments
     *
     * @param array{
     *   ID?: int,
     *   ENTITY_ID?: int,
     *   ENTITY_TYPE?: string,
     *   AUTHOR_ID?: int,
     *   COMMENT?: string,
     *   FILES?: array,
     *   } $order
     *
     * @param array{
     *   ID?: int,
     *   ENTITY_ID?: int,
     *   ENTITY_TYPE?: string,
     *   AUTHOR_ID?: int,
     *   COMMENT?: string,
     *   FILES?: array,
     *   } $filter
     * @param array    $select = ['ID','ENTITY_ID','ENTITY_TYPE','AUTHOR_ID','COMMENT','FILES']
     *
     * @return Generator<int, CommentItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'crm.timeline.comment.list',
        'https://apidocs.bitrix24.com/api-reference/crm/timeline/comments/crm-timeline-comment-list.html',
        'Batch list method for comments'
    )]
    public function list(array $order, array $filter, array $select, ?int $limit = null): Generator
    {
        $this->log->debug(
            'batchList',
            [
                'order'  => $order,
                'filter' => $filter,
                'select' => $select,
                'limit'  => $limit,
            ]
        );
        foreach ($this->batch->getTraversableList('crm.timeline.comment.list', $order, $filter, $select, $limit) as $key => $value) {
            yield $key => new CommentItemResult($value);
        }
    }

    /**
     * Batch adding comments
     *
     * @param array <int, array{
     *   ID?: int,
     *   ENTITY_ID?: int,
     *   ENTITY_TYPE?: string,
     *   AUTHOR_ID?: int,
     *   COMMENT?: string,
     *   FILES?: array,
     *   }> $comments
     *
     * @return Generator<int, AddedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'crm.comment.add',
        'https://apidocs.bitrix24.com/api-reference/crm/timeline/comments/crm-timeline-comment-add.html',
        'Batch adding comments'
    )]
    public function add(array $comments): Generator
    {
        $items = [];
        foreach ($comments as $item) {
            $items[] = [
                'fields' => $item,
            ];
        }

        foreach ($this->batch->addEntityItems('crm.timeline.comment.add', $items) as $key => $item) {
            yield $key => new AddedItemBatchResult($item);
        }
    }

    /**
     * Batch update comments
     *
     * Update elements in array with structure
     * element_id => [  // comment id
     *  'fields' => [] // comment fields to update
     * ]
     *
     * @param array<int, array> $entityItems
     * @return Generator<int, UpdatedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'crm.timeline.comment.update',
        'https://apidocs.bitrix24.com/api-reference/crm/timeline/comments/crm-timeline-comment-update.html',
        'Update in batch mode a list of comments'
    )]
    public function update(array $entityItems): Generator
    {
        foreach ($this->batch->updateEntityItems('crm.timeline.comment.update', $entityItems) as $key => $item) {
            yield $key => new UpdatedItemBatchResult($item);
        }
    }

    /**
     * Batch delete comments
     *
     * @param int[] $commentId
     *
     * @return Generator<int, DeletedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'crm.timeline.comment.delete',
        'https://apidocs.bitrix24.com/api-reference/crm/timeline/comments/crm-timeline-comment-delete.html',
        'Batch delete comments'
    )]
    public function delete(array $commentId): Generator
    {
        foreach ($this->batch->deleteEntityItems('crm.timeline.comment.delete', $commentId) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }
}
