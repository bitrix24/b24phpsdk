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

namespace Bitrix24\SDK\Services\Booking\BookingClient\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Booking\BookingClient\Result\BookingClientsResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['booking']))]
class BookingClient extends AbstractService
{
    public function __construct(CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Returns clients linked to a booking.
     *
     * @link https://apidocs.bitrix24.com/api-reference/booking/booking/client/booking-v1-booking-client-list.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'booking.v1.booking.client.list',
        'https://apidocs.bitrix24.com/api-reference/booking/booking/client/booking-v1-booking-client-list.html',
        'Returns clients linked to a booking.'
    )]
    public function list(int $bookingId): BookingClientsResult
    {
        return new BookingClientsResult(
            $this->core->call('booking.v1.booking.client.list', [
                'bookingId' => $bookingId,
            ])
        );
    }

    /**
     * Sets clients for a booking.
     *
     * @link https://apidocs.bitrix24.com/api-reference/booking/booking/client/booking-v1-booking-client-set.html
     *
     * @param array<int, array<string, mixed>> $clients
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'booking.v1.booking.client.set',
        'https://apidocs.bitrix24.com/api-reference/booking/booking/client/booking-v1-booking-client-set.html',
        'Sets clients for a booking.'
    )]
    public function set(int $bookingId, array $clients): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call('booking.v1.booking.client.set', [
                'bookingId' => $bookingId,
                'clients' => $clients,
            ])
        );
    }

    /**
     * Removes clients from a booking.
     *
     * @link https://apidocs.bitrix24.com/api-reference/booking/booking/client/booking-v1-booking-client-unset.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'booking.v1.booking.client.unset',
        'https://apidocs.bitrix24.com/api-reference/booking/booking/client/booking-v1-booking-client-unset.html',
        'Removes clients from a booking.'
    )]
    public function unset(int $bookingId): UpdatedItemResult
    {
        return new UpdatedItemResult(
            $this->core->call('booking.v1.booking.client.unset', [
                'bookingId' => $bookingId,
            ])
        );
    }
}
