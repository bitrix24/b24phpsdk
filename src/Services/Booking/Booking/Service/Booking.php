<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Veronica Akhmetova <264936994+fatestr1ngs@users.noreply.github.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Booking\Booking\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\AddedItemResult;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Booking\Booking\Result\AddedBookingResult;
use Bitrix24\SDK\Services\Booking\Booking\Result\BookingResult;
use Bitrix24\SDK\Services\Booking\Booking\Result\BookingsResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['booking']))]
class Booking extends AbstractService
{
    public function __construct(CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Adds a booking.
     *
     * @link https://apidocs.bitrix24.com/api-reference/booking/booking/booking-v1-booking-add.html
     *
     * @param array<string, mixed> $fields
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'booking.v1.booking.add',
        'https://apidocs.bitrix24.com/api-reference/booking/booking/booking-v1-booking-add.html',
        'Adds a booking.'
    )]
    public function add(array $fields): AddedBookingResult
    {
        return new AddedBookingResult(
            $this->core->call('booking.v1.booking.add', [
                'fields' => $fields,
            ])
        );
    }

    /**
     * Creates a booking from a waitlist entry.
     *
     * @link https://apidocs.bitrix24.com/api-reference/booking/booking/booking-v1-booking-createfromwaitlist.html
     *
     * @param array<string, mixed> $fields
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'booking.v1.booking.createfromwaitlist',
        'https://apidocs.bitrix24.com/api-reference/booking/booking/booking-v1-booking-createfromwaitlist.html',
        'Creates a booking from a waitlist entry.'
    )]
    public function createFromWaitlist(int $waitListId, array $fields = []): AddedItemResult
    {
        return new AddedItemResult(
            $this->core->call('booking.v1.booking.createfromwaitlist', [
                'waitListId' => $waitListId,
                'fields' => $fields,
            ])
        );
    }

    /**
     * Updates a booking.
     *
     * @link https://apidocs.bitrix24.com/api-reference/booking/booking/booking-v1-booking-update.html
     *
     * @param array<string, mixed> $fields
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'booking.v1.booking.update',
        'https://apidocs.bitrix24.com/api-reference/booking/booking/booking-v1-booking-update.html',
        'Updates a booking.'
    )]
    public function update(int $id, array $fields): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call('booking.v1.booking.update', [
                'id' => $id,
                'fields' => $fields,
            ])
        );
    }

    /**
     * Retrieves a booking.
     *
     * @link https://apidocs.bitrix24.com/api-reference/booking/booking/booking-v1-booking-get.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'booking.v1.booking.get',
        'https://apidocs.bitrix24.com/api-reference/booking/booking/booking-v1-booking-get.html',
        'Retrieves a booking.'
    )]
    public function get(int $id): BookingResult
    {
        return new BookingResult(
            $this->core->call('booking.v1.booking.get', [
                'id' => $id,
            ])
        );
    }

    /**
     * Retrieves a list of bookings.
     *
     * @link https://apidocs.bitrix24.com/api-reference/booking/booking/booking-v1-booking-list.html
     *
     * @param array<string, mixed> $filter
     * @param array<string, string> $order
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'booking.v1.booking.list',
        'https://apidocs.bitrix24.com/api-reference/booking/booking/booking-v1-booking-list.html',
        'Retrieves a list of bookings.'
    )]
    public function list(array $filter = [], array $order = []): BookingsResult
    {
        return new BookingsResult(
            $this->core->call('booking.v1.booking.list', [
                'filter' => $filter,
                'order' => $order,
            ])
        );
    }

    /**
     * Deletes a booking.
     *
     * @link https://apidocs.bitrix24.com/api-reference/booking/booking/booking-v1-booking-delete.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'booking.v1.booking.delete',
        'https://apidocs.bitrix24.com/api-reference/booking/booking/booking-v1-booking-delete.html',
        'Deletes a booking.'
    )]
    public function delete(int $id): DeletedItemResult
    {
        return new DeletedItemResult(
            $this->core->call('booking.v1.booking.delete', [
                'id' => $id,
            ])
        );
    }
}
