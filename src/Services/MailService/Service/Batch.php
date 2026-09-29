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

namespace Bitrix24\SDK\Services\MailService\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AddedItemBatchResult;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Core\Result\UpdatedItemBatchResult;
use Bitrix24\SDK\Services\MailService\Result\MailServiceItemResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['mailservice']))]
class Batch
{
    public function __construct(protected BatchOperationsInterface $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch list of mail services.
     *
     * @link https://apidocs.bitrix24.com/api-reference/mailservice/mailservice-list.html
     *
     * @return Generator<int, MailServiceItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'mailservice.list',
        'https://apidocs.bitrix24.com/api-reference/mailservice/mailservice-list.html',
        'Batch list of mail services'
    )]
    public function list(?int $limit = null): Generator
    {
        $this->log->debug(
            'batchList',
            [
                'limit' => $limit,
            ]
        );
        foreach ($this->batch->getTraversableList('mailservice.list', [], [], [], $limit) as $key => $value) {
            yield $key => new MailServiceItemResult($value);
        }
    }

    /**
     * Batch add mail services.
     *
     * @link https://apidocs.bitrix24.com/api-reference/mailservice/mailservice-add.html
     *
     * @param array<int, array{
     *   NAME: string,
     *   ACTIVE?: string,
     *   SERVER?: string,
     *   PORT?: int,
     *   ENCRYPTION?: string,
     *   LINK?: string,
     *   SORT?: int,
     * }> $mailServices
     *
     * @return Generator<int, AddedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'mailservice.add',
        'https://apidocs.bitrix24.com/api-reference/mailservice/mailservice-add.html',
        'Batch add mail services'
    )]
    public function add(array $mailServices): Generator
    {
        foreach ($this->batch->addEntityItems('mailservice.add', $mailServices) as $key => $item) {
            yield $key => new AddedItemBatchResult($item);
        }
    }

    /**
     * Batch update mail services.
     *
     * Array structure: [$mailServiceId => ['NAME' => '...', ...], ...]
     *
     * @link https://apidocs.bitrix24.com/api-reference/mailservice/mailservice-update.html
     *
     * @param array<int, array<string, mixed>> $mailServiceItems
     *
     * @return Generator<int, UpdatedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'mailservice.update',
        'https://apidocs.bitrix24.com/api-reference/mailservice/mailservice-update.html',
        'Batch update mail services'
    )]
    public function update(array $mailServiceItems): Generator
    {
        foreach ($this->batch->updateEntityItems('mailservice.update', $mailServiceItems) as $key => $item) {
            yield $key => new UpdatedItemBatchResult($item);
        }
    }

    /**
     * Batch delete mail services.
     *
     * @link https://apidocs.bitrix24.com/api-reference/mailservice/mailservice-delete.html
     *
     * @param int[] $mailServiceIds
     *
     * @return Generator<int, DeletedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'mailservice.delete',
        'https://apidocs.bitrix24.com/api-reference/mailservice/mailservice-delete.html',
        'Batch delete mail services'
    )]
    public function delete(array $mailServiceIds): Generator
    {
        foreach ($this->batch->deleteEntityItems('mailservice.delete', $mailServiceIds) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }
}
