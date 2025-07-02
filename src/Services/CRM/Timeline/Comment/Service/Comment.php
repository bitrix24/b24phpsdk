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
use Bitrix24\SDK\Services\CRM\Timeline\Comment\Result\CommentResult;
use Bitrix24\SDK\Services\CRM\Timeline\Comment\Result\CommentsResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['crm']))]
class Comment extends AbstractService
{
    /**
     * Comment constructor.
     */
    public function __construct(public Batch $batch, CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Adds a new comment to the timeline
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/timeline/comments/crm-timeline-comment-add.html
     *
     * @param array{
     *   ID?: int,
     *   ENTITY_ID?: int,
     *   ENTITY_TYPE?: string,
     *   AUTHOR_ID?: int,
     *   COMMENT?: string,
     *   FILES?: array,
     * } $fields
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.timeline.comment.add',
        'https://apidocs.bitrix24.com/api-reference/crm/timeline/comments/crm-timeline-comment-add.html',
        'Adds a new comment to the timeline'
    )]
    public function add(array $fields): AddedItemResult
    {
        return new AddedItemResult(
            $this->core->call(
                'crm.timeline.comment.add',
                [
                    'fields' => $fields
                ]
            )
        );
    }

    /**
     * Deletes a comment.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/timeline/comments/crm-timeline-comment-delete.html
     *
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.timeline.comment.delete',
        'https://apidocs.bitrix24.com/api-reference/crm/timeline/comments/crm-timeline-comment-delete.html',
        'Deletes a comment'
    )]
    public function delete(int $id, int $ownerTypeId = 0, int $ownerId = 0): DeletedItemResult
    {
        $params = [
            'id' => $id,
        ];
        if (!empty($ownerTypeId)) {
            $params = [
                'id' => $id,
                'ownerTypeId' => $ownerTypeId,
                'ownerId' => $ownerId,
            ];
        }
        return new DeletedItemResult(
            $this->core->call(
                'crm.timeline.comment.delete',
                $params
            )
        );
    }

    /**
     * Retrieves a list of timeline comment fields.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/timeline/comments/crm-timeline-comment-fields.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.timeline.comment.fields',
        'https://apidocs.bitrix24.com/api-reference/crm/timeline/comments/crm-timeline-comment-fields.html',
        'Retrieves a list of timeline comment fields'
    )]
    public function fields(): FieldsResult
    {
        return new FieldsResult($this->core->call('crm.timeline.comment.fields'));
    }

    /**
     * Retrieves information about a comment.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/timeline/comments/crm-timeline-comment-get.html
     *
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.timeline.comment.get',
        'https://apidocs.bitrix24.com/api-reference/crm/timeline/comments/crm-timeline-comment-get.html',
        'Retrieves information about a comment'
    )]
    public function get(int $id): CommentResult
    {
        return new CommentResult($this->core->call('crm.timeline.comment.get', ['id' => $id]));
    }

    /**
     * Retrieves a list of all comments for the CRM entity.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/timeline/comments/crm-timeline-comment-list.html
     *
     * @param array   $order     - order of comment items
     * @param array   $filter    - filter array
     * @param array   $select    = ['ID','ENTITY_ID','ENTITY_TYPE','AUTHOR_ID','COMMENT','FILES']
     * @param integer $startItem - entity number to start from (usually returned in 'next' field of previous 'crm.timeline.comment.list' API call)
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.timeline.comment.list',
        'https://apidocs.bitrix24.com/api-reference/crm/timeline/comments/crm-timeline-comment-list.html',
        'Retrieves a list of all comments for the CRM entity'
    )]
    public function list(array $order = [], array $filter = [], array $select = [], int $startItem = 0): CommentsResult
    {
        return new CommentsResult(
            $this->core->call(
                'crm.timeline.comment.list',
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
     * Updates a comment.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/timeline/comments/crm-timeline-comment-update.html
     *
     * @param array{
     *   ID?: int,
     *   ENTITY_ID?: int,
     *   ENTITY_TYPE?: string,
     *   AUTHOR_ID?: int,
     *   COMMENT?: string,
     *   FILES?: array,
     *   }        $fields
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.timeline.comment.update',
        'https://apidocs.bitrix24.com/api-reference/crm/timeline/comments/crm-timeline-comment-update.html',
        'Updates a comment'
    )]
    public function update(int $id, array $fields, int $ownerTypeId = 0, int $ownerId = 0): UpdatedItemResult
    {
        $params = [
            'id' => $id,
            'fields' => $fields
        ];
        if (!empty($ownerTypeId)) {
            $params = [
                'id' => $id,
                'fields' => $fields,
                'ownerTypeId' => $ownerTypeId,
                'ownerId' => $ownerId,
            ];
        }
        return new UpdatedItemResult(
            $this->core->call(
                'crm.timeline.comment.update',
                $params
            )
        );
    }

    /**
     * Count comments by filter
     *
     * @param array{
     *   ID?: int,
     *   ENTITY_ID?: int,
     *   ENTITY_TYPE?: string,
     *   AUTHOR_ID?: int,
     *   COMMENT?: string,
     *   FILES?: array,
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
