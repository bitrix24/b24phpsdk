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

namespace Bitrix24\SDK\Services\Booking\BookingExternalData\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\UpdatedItemBatchResult;
use Bitrix24\SDK\Services\Booking;
use Bitrix24\SDK\Services\Booking\BookingExternalData\Result\BookingExternalDataItemResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['booking']))]
class Batch
{
    public function __construct(protected Booking\Batch $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch read external data links of several bookings
     *
     * @link https://apidocs.bitrix24.com/api-reference/booking/booking/external-data/booking-v1-booking-externaldata-list.html
     *
     * @param int[] $bookingIds
     *
     * @return Generator<int, BookingExternalDataItemResult[]> booking id => external data links of this booking
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'booking.v1.booking.externalData.list',
        'https://apidocs.bitrix24.com/api-reference/booking/booking/external-data/booking-v1-booking-externaldata-list.html',
        'Batch read external data links of several bookings'
    )]
    public function list(array $bookingIds): Generator
    {
        $this->log->debug(
            'batchList',
            [
                'bookingIds' => $bookingIds,
            ]
        );

        $externalDataGenerator = $this->batch->getEntityItemsByParentIds(
            'booking.v1.booking.externalData.list',
            'bookingId',
            $bookingIds
        );

        foreach ($externalDataGenerator as $bookingId => $externalData) {
            yield $bookingId => array_map(
                static fn (array $item): BookingExternalDataItemResult => new BookingExternalDataItemResult($item),
                $externalData
            );
        }
    }

    /**
     * Batch set external data links for several bookings
     *
     * Elements in array with structure
     * booking id => [ // external data links replacing the ones currently stored for the booking
     *  ['moduleId' => 'crm', 'entityTypeId' => 'deal', 'value' => '1']
     * ]
     *
     * @link https://apidocs.bitrix24.com/api-reference/booking/booking/external-data/booking-v1-booking-externaldata-set.html
     *
     * @param array<int, array<int, array<string, string>>> $bookingExternalData
     *
     * @return Generator<int, UpdatedItemBatchResult> booking id => set result
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'booking.v1.booking.externalData.set',
        'https://apidocs.bitrix24.com/api-reference/booking/booking/external-data/booking-v1-booking-externaldata-set.html',
        'Batch set external data links for several bookings'
    )]
    public function set(array $bookingExternalData): Generator
    {
        $setGenerator = $this->batch->setEntityItems(
            'booking.v1.booking.externalData.set',
            'bookingId',
            'externalData',
            $bookingExternalData
        );

        foreach ($setGenerator as $bookingId => $item) {
            yield $bookingId => new UpdatedItemBatchResult($item);
        }
    }
}
