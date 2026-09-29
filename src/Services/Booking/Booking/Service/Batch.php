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

namespace Bitrix24\SDK\Services\Booking\Booking\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AddedItemBatchResult;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Core\Result\UpdatedItemBatchResult;
use Bitrix24\SDK\Services\Booking;
use Bitrix24\SDK\Services\Booking\Booking\Result\BookingItemResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['booking']))]
class Batch
{
    public function __construct(protected Booking\Batch $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch list of bookings
     *
     * @link https://apidocs.bitrix24.com/api-reference/booking/booking/booking-v1-booking-list.html
     *
     * @param array<string, mixed> $filter
     * @param array<string, string> $order
     *
     * @return Generator<int, BookingItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'booking.v1.booking.list',
        'https://apidocs.bitrix24.com/api-reference/booking/booking/booking-v1-booking-list.html',
        'Batch list of bookings'
    )]
    public function list(array $filter = [], array $order = [], ?int $limit = null): Generator
    {
        $this->log->debug(
            'batchList',
            [
                'filter' => $filter,
                'order' => $order,
                'limit' => $limit,
            ]
        );

        foreach ($this->batch->getTraversableList('booking.v1.booking.list', $order, $filter, [], $limit) as $key => $value) {
            yield $key => new BookingItemResult($value);
        }
    }

    /**
     * Batch adding bookings
     *
     * @link https://apidocs.bitrix24.com/api-reference/booking/booking/booking-v1-booking-add.html
     *
     * @param array<int, array<string, mixed>> $bookings
     *
     * @return Generator<int, AddedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'booking.v1.booking.add',
        'https://apidocs.bitrix24.com/api-reference/booking/booking/booking-v1-booking-add.html',
        'Batch adding bookings'
    )]
    public function add(array $bookings): Generator
    {
        $items = [];
        foreach ($bookings as $booking) {
            $items[] = ['fields' => $booking];
        }

        foreach ($this->batch->addEntityItems('booking.v1.booking.add', $items) as $key => $item) {
            yield $key => new AddedItemBatchResult($item);
        }
    }

    /**
     * Batch update bookings
     *
     * Update elements in array with structure
     * booking id => [ // fields to update
     *  'name' => 'string',
     *  'description' => 'string',
     *  'resourceIds' => [1, 2],
     *  'datePeriod' => []
     * ]
     *
     * @link https://apidocs.bitrix24.com/api-reference/booking/booking/booking-v1-booking-update.html
     *
     * @param array<int, array<string, mixed>> $bookings
     *
     * @return Generator<int, UpdatedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'booking.v1.booking.update',
        'https://apidocs.bitrix24.com/api-reference/booking/booking/booking-v1-booking-update.html',
        'Batch update bookings'
    )]
    public function update(array $bookings): Generator
    {
        $items = [];
        foreach ($bookings as $bookingId => $fields) {
            $items[$bookingId] = ['fields' => $fields];
        }

        foreach ($this->batch->updateEntityItems('booking.v1.booking.update', $items) as $key => $item) {
            yield $key => new UpdatedItemBatchResult($item);
        }
    }

    /**
     * Batch delete bookings
     *
     * @link https://apidocs.bitrix24.com/api-reference/booking/booking/booking-v1-booking-delete.html
     *
     * @param int[] $bookingIds
     *
     * @return Generator<int, DeletedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'booking.v1.booking.delete',
        'https://apidocs.bitrix24.com/api-reference/booking/booking/booking-v1-booking-delete.html',
        'Batch delete bookings'
    )]
    public function delete(array $bookingIds): Generator
    {
        foreach ($this->batch->deleteEntityItems('booking.v1.booking.delete', $bookingIds) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }
}
