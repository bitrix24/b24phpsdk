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

namespace Bitrix24\SDK\Services\Booking\Waitlist\Service;

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
use Bitrix24\SDK\Services\Booking\Waitlist\Result\AddedWaitlistResult;
use Bitrix24\SDK\Services\Booking\Waitlist\Result\WaitlistResult;
use Bitrix24\SDK\Services\Booking\Waitlist\Result\WaitlistsResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['booking']))]
class Waitlist extends AbstractService
{
    public function __construct(CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Adds a waitlist entry.
     *
     * @link https://apidocs.bitrix24.com/api-reference/booking/waitlist/booking-v1-waitlist-add.html
     *
     * @param array<string, mixed> $fields
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'booking.v1.waitlist.add',
        'https://apidocs.bitrix24.com/api-reference/booking/waitlist/booking-v1-waitlist-add.html',
        'Adds a waitlist entry.'
    )]
    public function add(array $fields): AddedWaitlistResult
    {
        return new AddedWaitlistResult(
            $this->core->call('booking.v1.waitlist.add', [
                'fields' => $fields,
            ])
        );
    }

    /**
     * Creates a waitlist entry from a booking.
     *
     * @link https://apidocs.bitrix24.com/api-reference/booking/waitlist/booking-v1-waitlist-createfrombooking.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'booking.v1.waitlist.createfrombooking',
        'https://apidocs.bitrix24.com/api-reference/booking/waitlist/booking-v1-waitlist-createfrombooking.html',
        'Creates a waitlist entry from a booking.'
    )]
    public function createFromBooking(int $bookingId): AddedItemResult
    {
        return new AddedItemResult(
            $this->core->call('booking.v1.waitlist.createfrombooking', [
                'bookingId' => $bookingId,
            ])
        );
    }

    /**
     * Updates a waitlist entry.
     *
     * @link https://apidocs.bitrix24.com/api-reference/booking/waitlist/booking-v1-waitlist-update.html
     *
     * @param array<string, mixed> $fields
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'booking.v1.waitlist.update',
        'https://apidocs.bitrix24.com/api-reference/booking/waitlist/booking-v1-waitlist-update.html',
        'Updates a waitlist entry.'
    )]
    public function update(int $id, array $fields): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call('booking.v1.waitlist.update', [
                'id' => $id,
                'fields' => $fields,
            ])
        );
    }

    /**
     * Retrieves a waitlist entry.
     *
     * @link https://apidocs.bitrix24.com/api-reference/booking/waitlist/booking-v1-waitlist-get.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'booking.v1.waitlist.get',
        'https://apidocs.bitrix24.com/api-reference/booking/waitlist/booking-v1-waitlist-get.html',
        'Retrieves a waitlist entry.'
    )]
    public function get(int $id): WaitlistResult
    {
        return new WaitlistResult(
            $this->core->call('booking.v1.waitlist.get', [
                'id' => $id,
            ])
        );
    }

    /**
     * Retrieves a list of waitlist entries.
     *
     * @link https://apidocs.bitrix24.com/api-reference/booking/waitlist/booking-v1-waitlist-list.html
     *
     * @param array<string, mixed> $filter
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'booking.v1.waitlist.list',
        'https://apidocs.bitrix24.com/api-reference/booking/waitlist/booking-v1-waitlist-list.html',
        'Retrieves a list of waitlist entries.'
    )]
    public function list(array $filter = []): WaitlistsResult
    {
        return new WaitlistsResult(
            $this->core->call('booking.v1.waitlist.list', [
                'filter' => $filter,
            ])
        );
    }

    /**
     * Deletes a waitlist entry.
     *
     * @link https://apidocs.bitrix24.com/api-reference/booking/waitlist/booking-v1-waitlist-delete.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'booking.v1.waitlist.delete',
        'https://apidocs.bitrix24.com/api-reference/booking/waitlist/booking-v1-waitlist-delete.html',
        'Deletes a waitlist entry.'
    )]
    public function delete(int $id): DeletedItemResult
    {
        return new DeletedItemResult(
            $this->core->call('booking.v1.waitlist.delete', [
                'id' => $id,
            ])
        );
    }
}
