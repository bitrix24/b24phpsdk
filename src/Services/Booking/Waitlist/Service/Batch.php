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

namespace Bitrix24\SDK\Services\Booking\Waitlist\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AddedItemBatchResult;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Core\Result\UpdatedItemBatchResult;
use Bitrix24\SDK\Services\Booking;
use Bitrix24\SDK\Services\Booking\Waitlist\Result\WaitlistItemResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['booking']))]
class Batch
{
    public function __construct(protected Booking\Batch $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch list of waitlist entries
     *
     * @link https://apidocs.bitrix24.com/api-reference/booking/waitlist/booking-v1-waitlist-list.html
     *
     * @param array<string, mixed> $filter
     *
     * @return Generator<int, WaitlistItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'booking.v1.waitlist.list',
        'https://apidocs.bitrix24.com/api-reference/booking/waitlist/booking-v1-waitlist-list.html',
        'Batch list of waitlist entries'
    )]
    public function list(array $filter = [], ?int $limit = null): Generator
    {
        $this->log->debug(
            'batchList',
            [
                'filter' => $filter,
                'limit' => $limit,
            ]
        );

        foreach ($this->batch->getTraversableList('booking.v1.waitlist.list', [], $filter, [], $limit) as $key => $value) {
            yield $key => new WaitlistItemResult($value);
        }
    }

    /**
     * Batch adding waitlist entries
     *
     * @link https://apidocs.bitrix24.com/api-reference/booking/waitlist/booking-v1-waitlist-add.html
     *
     * @param array<int, array<string, mixed>> $waitlists
     *
     * @return Generator<int, AddedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'booking.v1.waitlist.add',
        'https://apidocs.bitrix24.com/api-reference/booking/waitlist/booking-v1-waitlist-add.html',
        'Batch adding waitlist entries'
    )]
    public function add(array $waitlists): Generator
    {
        $items = [];
        foreach ($waitlists as $waitlist) {
            $items[] = ['fields' => $waitlist];
        }

        foreach ($this->batch->addEntityItems('booking.v1.waitlist.add', $items) as $key => $item) {
            yield $key => new AddedItemBatchResult($item);
        }
    }

    /**
     * Batch update waitlist entries
     *
     * Update elements in array with structure
     * waitlist id => [ // fields to update
     *  'note' => 'string',
     *  'resourceIds' => [1, 2],
     *  'clients' => []
     * ]
     *
     * @link https://apidocs.bitrix24.com/api-reference/booking/waitlist/booking-v1-waitlist-update.html
     *
     * @param array<int, array<string, mixed>> $waitlists
     *
     * @return Generator<int, UpdatedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'booking.v1.waitlist.update',
        'https://apidocs.bitrix24.com/api-reference/booking/waitlist/booking-v1-waitlist-update.html',
        'Batch update waitlist entries'
    )]
    public function update(array $waitlists): Generator
    {
        $items = [];
        foreach ($waitlists as $waitlistId => $fields) {
            $items[$waitlistId] = ['fields' => $fields];
        }

        foreach ($this->batch->updateEntityItems('booking.v1.waitlist.update', $items) as $key => $item) {
            yield $key => new UpdatedItemBatchResult($item);
        }
    }

    /**
     * Batch delete waitlist entries
     *
     * @link https://apidocs.bitrix24.com/api-reference/booking/waitlist/booking-v1-waitlist-delete.html
     *
     * @param int[] $waitlistIds
     *
     * @return Generator<int, DeletedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'booking.v1.waitlist.delete',
        'https://apidocs.bitrix24.com/api-reference/booking/waitlist/booking-v1-waitlist-delete.html',
        'Batch delete waitlist entries'
    )]
    public function delete(array $waitlistIds): Generator
    {
        foreach ($this->batch->deleteEntityItems('booking.v1.waitlist.delete', $waitlistIds) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }
}
