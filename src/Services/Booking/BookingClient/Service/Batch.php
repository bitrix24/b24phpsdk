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

namespace Bitrix24\SDK\Services\Booking\BookingClient\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\UpdatedItemBatchResult;
use Bitrix24\SDK\Services\Booking;
use Bitrix24\SDK\Services\Booking\BookingClient\Result\BookingClientItemResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['booking']))]
class Batch
{
    public function __construct(protected Booking\Batch $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch read clients linked to several bookings
     *
     * @link https://apidocs.bitrix24.com/api-reference/booking/booking/client/booking-v1-booking-client-list.html
     *
     * @param int[] $bookingIds
     *
     * @return Generator<int, BookingClientItemResult[]> booking id => clients of this booking
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'booking.v1.booking.client.list',
        'https://apidocs.bitrix24.com/api-reference/booking/booking/client/booking-v1-booking-client-list.html',
        'Batch read clients linked to several bookings'
    )]
    public function list(array $bookingIds): Generator
    {
        $this->log->debug(
            'batchList',
            [
                'bookingIds' => $bookingIds,
            ]
        );

        $clientsGenerator = $this->batch->getEntityItemsByParentIds(
            'booking.v1.booking.client.list',
            'bookingId',
            $bookingIds
        );

        foreach ($clientsGenerator as $bookingId => $clients) {
            yield $bookingId => array_map(
                static fn (array $client): BookingClientItemResult => new BookingClientItemResult($client),
                $clients
            );
        }
    }

    /**
     * Batch set clients for several bookings
     *
     * Elements in array with structure
     * booking id => [ // clients replacing the ones currently linked to the booking
     *  ['id' => '1', 'type' => ['module' => 'crm', 'code' => 'CONTACT']]
     * ]
     *
     * @link https://apidocs.bitrix24.com/api-reference/booking/booking/client/booking-v1-booking-client-set.html
     *
     * @param array<int, array<int, array<string, mixed>>> $bookingClients
     *
     * @return Generator<int, UpdatedItemBatchResult> booking id => set result
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'booking.v1.booking.client.set',
        'https://apidocs.bitrix24.com/api-reference/booking/booking/client/booking-v1-booking-client-set.html',
        'Batch set clients for several bookings'
    )]
    public function set(array $bookingClients): Generator
    {
        $setGenerator = $this->batch->setEntityItems(
            'booking.v1.booking.client.set',
            'bookingId',
            'clients',
            $bookingClients
        );

        foreach ($setGenerator as $bookingId => $item) {
            yield $bookingId => new UpdatedItemBatchResult($item);
        }
    }
}
